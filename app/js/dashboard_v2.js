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

    // --- RENDER CHARTS ---
    if (window.Chart){
      const palette = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899'];
      const bgColors = (donut.labels || []).map((l, i) => palette[i % palette.length]);

      // DONUT ANIMATION: Start at 0, then update
      const donutCtx = document.getElementById('chartDonut').getContext('2d');
      const myDonut = new Chart(donutCtx, { 
        type: 'doughnut', 
        data: { 
          labels: donut.labels, 
          datasets:[{ 
            data: donut.labels.map(() => 0), 
            backgroundColor: bgColors, 
            borderWidth: 2, 
            borderColor: '#ffffff'
          }] 
        }, 
        options: { 
          cutout: '65%', 
          rotation: -90, // Reset to Top Center
          animation: { animateRotate: true, duration: 2500, easing: 'easeInOutQuart' },
          plugins:{ legend:{ display:false } } 
        } 
      });

      setTimeout(() => {
        myDonut.data.datasets[0].data = donut.data;
        myDonut.update();
      }, 350);

      // Bar Chart
      const barCtx = document.getElementById('chartBar').getContext('2d');
      const gradient1 = barCtx.createLinearGradient(0, 0, 0, 400);
      gradient1.addColorStop(0, '#6366f1'); gradient1.addColorStop(1, '#a5b4fc');
      const gradient2 = barCtx.createLinearGradient(0, 0, 0, 400);
      gradient2.addColorStop(0, '#10b981'); gradient2.addColorStop(1, '#6ee7b7');

      new Chart(barCtx, { 
        type: 'bar', 
        data: { 
          labels: attendance.labels, 
          datasets: [
            { label:'Submissions', data: attendance.present, backgroundColor: gradient1, borderRadius: 8, barThickness: 12 },
            { label:'Reviews', data: attendance.absent, backgroundColor: gradient2, borderRadius: 8, barThickness: 12 }
          ] 
        }, 
        options: { 
          responsive: true, 
          animation: { delay: 500, duration: 1500 },
          scales: {
            y: { beginAtZero: true, grid: { display: true, color: '#f1f5f9' }, ticks: { font: { weight: 'bold' } } },
            x: { grid: { display: true, color: '#f1f5f9' }, ticks: { font: { weight: 'bold' } } }
          },
          plugins:{ legend:{ position:'top', labels: { usePointStyle: true, boxWidth: 6, font: { weight: 'bold' } } } } 
        } 
      });

      // Legend
      $('#donutCenter').textContent = donut.center;
      $('#donutLegend').innerHTML = (donut.labels || []).map((l, i) => `
        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:border-indigo-200 transition-all group" data-tooltip="${l}">
          <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-3 h-3 rounded-full shadow-sm flex-none" style="background:${bgColors[i]}"></div>
            <span class="text-sm font-bold text-slate-600 truncate">${l}</span>
          </div>
          <span class="text-xs font-black px-2 py-1 rounded-lg bg-white text-slate-800 shadow-sm border border-slate-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors flex-none">${donut.data[i] ?? 0}</span>
        </div>
      `).join('');
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

    // 2. ASYNC LOAD: AI Analytics
    const handleAI = async () => {
      try {
        const aiRes = await fetch('dashboard_api.php?action=ai_analytics', {credentials:'same-origin'});
        const aiJson = await aiRes.json();
        if (!aiJson.ok) return;
        
        const ai_analytics = aiJson.data;
        console.log(`🤖 AI: Summary via ${ai_analytics.model || 'Unknown'}`);

        const summaryEl = document.getElementById('aiSummary');
        const actionEl = document.getElementById('aiAction');
        const progContainer = document.getElementById('aiProgressBars');
        
        if (!summaryEl || !actionEl || !progContainer) return;
        
        let aiData;
        try {
          aiData = typeof ai_analytics.summary === 'string' ? JSON.parse(ai_analytics.summary) : ai_analytics.summary;
        } catch (e) {
          aiData = { summary: "Analyzing institutional compliance...", action: "Review program indicators." };
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

        progContainer.innerHTML = (ai_analytics.stats || []).map(s => {
          const p = s.percentage || 0;
          return `<div class="ai-progress-item flex items-center gap-6"><div class="flex-1"><div class="flex justify-between items-center mb-2"><span class="text-sm font-bold text-slate-700">${s.program}</span><span class="text-[10px] font-black tracking-widest text-indigo-600">${p}% COMPLETE</span></div><div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden"><div class="bg-indigo-600 h-full rounded-full transition-all duration-1000 ease-in-out" style="width: ${p}%"></div></div></div></div>`;
        }).join('');
      } catch (err) { console.warn('AI load error:', err); }
    };
    
    handleAI();
  } catch(err){ console.warn('Dashboard error:', err); }
})();
