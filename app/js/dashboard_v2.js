(async function(){
  const $ = (sel) => document.querySelector(sel);
  const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  
  try {
    // 1. FAST LOAD: Get basic stats and charts
    const r = await fetch('dashboard_api.php?action=summary', {credentials:'same-origin'});
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || 'Failed');
    
    const { totals, donut, attendance, notices, events } = j.data;
    
    // Set KPIs
    setText('kpiPrograms', totals.programs ?? '0');
    setText('kpiVisits', totals.visits ?? '0');
    setText('kpiUsers', totals.users ?? '0');
    setText('kpiDocuments', totals.documents ?? '0');

    // Render Charts
    if (window.Chart){
      const donutCtx = document.getElementById('chartDonut').getContext('2d');
      const palette = ['#3B82F6','#F59E0B','#22D3EE','#10B981','#6366F1','#EF4444'];
      const bgColors = (donut.labels || []).map((l, i) => palette[i % palette.length]);

      new Chart(donutCtx, { 
        type: 'doughnut', 
        data: { labels: donut.labels, datasets:[{ data: donut.data, backgroundColor: bgColors, borderWidth: 0 }] }, 
        options: { cutout: '70%', plugins:{legend:{display:false}} } 
      });

      $('#donutCenter').textContent = donut.center;
      $('#donutLegend').innerHTML = (donut.labels || []).map((l, i) => 
        `<span class="inline-flex items-center mr-4 mb-2"><span style="display:inline-block;width:10px;height:10px;border-radius:9999px;background:${bgColors[i]};margin-right:8px;"></span>${l}: ${donut.data[i] ?? 0}</span>`
      ).join('');

      const barCtx = document.getElementById('chartBar').getContext('2d');
      new Chart(barCtx, { type: 'bar', data: { labels: attendance.labels, datasets: [{label:'Submissions', data: attendance.present},{label:'Reviews', data: attendance.absent}] }, options: { responsive: true, plugins:{legend:{position:'top'}} } });
    }

    // Render Notices & Calendar
    const list = document.getElementById('noticeList');
    list.innerHTML = (notices||[]).map(n => `<li class="notice-item"><div><div style="font-weight:600">${n.title}</div><div class="notice-meta"><span>${n.date}</span><span><i class="fa-regular fa-eye"></i> ${n.views}</span></div></div><div>⋮</div></li>`).join('');
    
    const cal = $('#calendar'); const now = new Date(); let ym = { y: now.getFullYear(), m: now.getMonth() };
    const evSet = new Set((events||[]).map(e=>e.date));
    const renderCal = () => {
      const first = new Date(ym.y, ym.m, 1), last = new Date(ym.y, ym.m+1, 0);
      const monthName = first.toLocaleString(undefined, {month:'long', year:'numeric'});
      let html = `<div class="cal-header"><button class="btn-primary" id="calPrev"><i class="fa-solid fa-chevron-left"></i></button><div style="font-weight:600">${monthName}</div><button class="btn-primary" id="calNext"><i class="fa-solid fa-chevron-right"></i></button></div>`;
      html += `<table><thead><tr>${['Su','Mo','Tu','We','Th','Fr','Sa'].map(d=>`<th>${d}</th>`).join('')}</tr></thead><tbody>`;
      let day = 1, started = false;
      for(let r=0;r<6;r++){ html += '<tr>'; for(let c=0;c<7;c++){ const isStart = (!started && c===first.getDay()); if (!started && !isStart){ html += '<td></td>'; continue; } started = true; if (day>last.getDate()){ html += '<td></td>'; continue; } const dstr = `${ym.y}-${String(ym.m+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`; const cls = []; if (evSet.has(dstr)) cls.push('event'); const td = new Date(); if (ym.y===td.getFullYear() && ym.m===td.getMonth() && day===td.getDate()) cls.push('today'); html += `<td class="${cls.join(' ')}">${day}</td>`; day++; } html += '</tr>'; } html += '</tbody></table>'; cal.innerHTML = html; document.getElementById('calPrev').onclick = ()=>{ ym.m--; if (ym.m<0){ ym.y--; ym.m=11; } renderCal(); }; document.getElementById('calNext').onclick = ()=>{ ym.m++; if (ym.m>11){ ym.y++; ym.m=0; } renderCal(); }; };
    renderCal();

    // 2. ASYNC LOAD: Get AI Analytics in background
    const handleAI = async () => {
      console.log('🤖 AI: Starting background analysis...');
      try {
        const aiRes = await fetch('dashboard_api.php?action=ai_analytics', {credentials:'same-origin'});
        const aiJson = await aiRes.json();
        if (!aiJson.ok) {
          console.error('🤖 AI Error (API):', aiJson.error || 'Unknown server error');
          return;
        }
        
        const ai_analytics = aiJson.data;
        if (ai_analytics.summary) {
          console.log('🤖 AI: Summary received successfully.');
          if (ai_analytics.summary.includes('AI Error:') || ai_analytics.summary.includes('API Error:')) {
            console.error('🤖 AI Error Detected:', ai_analytics.summary);
          }
        }

        const summaryEl = document.getElementById('aiSummary');
        const actionEl = document.getElementById('aiAction');
        
        let aiData;
        try {
          aiData = typeof ai_analytics.summary === 'string' ? JSON.parse(ai_analytics.summary) : ai_analytics.summary;
        } catch (e) {
          aiData = { summary: "Institutional compliance metrics are being analyzed.", action: "Review program indicators for improvement." };
        }

        const typeText = (el, text, speed, callback) => {
          let i = 0; el.innerHTML = ''; el.classList.add('typing-cursor');
          const interval = setInterval(() => {
            if (i < text.length) { el.innerHTML += text.charAt(i); i++; }
            else { clearInterval(interval); el.classList.remove('typing-cursor'); if (callback) callback(); }
          }, speed);
        };

        typeText(summaryEl, aiData.summary || '', 10, () => {
          typeText(actionEl, aiData.action || '', 8);
        });

        const progContainer = document.getElementById('aiProgressBars');
        if (progContainer) {
          progContainer.innerHTML = (ai_analytics.stats || []).map(s => {
            const p = s.percentage || 0;
            return `
              <div class="ai-progress-item flex items-center gap-6">
                <div class="flex-1">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-bold text-slate-700">${s.program}</span>
                    <span class="text-[10px] font-black tracking-widest text-indigo-600">${p}% COMPLETE</span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-indigo-600 h-full rounded-full transition-all duration-1000 ease-in-out" style="width: ${p}%"></div>
                  </div>
                </div>
              </div>`;
          }).join('');
        }
      } catch (err) {
        console.warn('AI load error:', err);
      }
    };
    
    handleAI();

  } catch(err){
    console.warn('Dashboard error:', err);
  }
})();
