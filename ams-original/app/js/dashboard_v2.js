(async function(){
  const $ = (sel) => document.querySelector(sel);
  const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  try {
    const r = await fetch('dashboard_api.php?action=summary', {credentials:'same-origin'});
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || 'Failed');
    const { totals, donut, attendance, notices, events } = j.data;
    setText('kpiPrograms', totals.programs ?? '0');
    setText('kpiVisits', totals.visits ?? '0');
    setText('kpiUsers', totals.users ?? '0');
    setText('kpiDocuments', totals.documents ?? '0');
    if (window.Chart){
      
    
    
    const donutCtx = document.getElementById('chartDonut').getContext('2d');

    // Name-based override colors (keep Business Admin as cyan, etc.)
    const colorMap = {
      'information technology': '#3B82F6',   // blue
      'criminology':            '#F59E0B',   // amber
      'business administration':'#22D3EE',   // cyan
      'education':              '#10B981',   // green
      'hospitality management': '#6366F1'    // indigo
    };
    // Long fallback palette (will cycle if more labels)
    const palette = [
      '#3B82F6','#F59E0B','#22D3EE','#10B981','#6366F1',
      '#EF4444','#14B8A6','#A855F7','#F43F5E','#84CC16',
      '#FB923C','#06B6D4','#8B5CF6','#0EA5E9','#E11D48'
    ];
    const pickColorByIndex = (i) => palette[i % palette.length];
    const pickColorByName = (label, i) => {
      const key = String(label||'').toLowerCase();
      for (const k in colorMap) { if (key.includes(k)) return colorMap[k]; }
      return pickColorByIndex(i);
    };
    const bgColors = (donut.labels || []).map((l, i) => pickColorByName(l, i));

    new Chart(donutCtx, { 
      type: 'doughnut', 
      data: { 
        labels: donut.labels, 
        datasets:[{ data: donut.data, backgroundColor: bgColors, borderWidth: 0 }] 
      }, 
      options: { cutout: '70%', plugins:{legend:{display:false}} } 
    });

    $('#donutCenter').textContent = donut.center;
    $('#donutLegend').innerHTML = (donut.labels || []).map((l, i) => 
      `<span class="inline-flex items-center mr-4 mb-2">
         <span style="display:inline-block;width:10px;height:10px;border-radius:9999px;background:${bgColors[i]};margin-right:8px;"></span>
         ${l}: ${donut.data[i] ?? 0}
       </span>`
    ).join('');
const barCtx = document.getElementById('chartBar').getContext('2d');
      new Chart(barCtx, { type: 'bar', data: { labels: attendance.labels, datasets: [{label:'Submissions', data: attendance.present},{label:'Reviews', data: attendance.absent}] }, options: { responsive: true, plugins:{legend:{position:'top'}} } });
    }
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
  } catch(err){
    console.warn('Dashboard v2 error:', err);
    ['kpiPrograms','kpiVisits','kpiUsers','kpiDocuments'].forEach(id=>{ const el = document.getElementById(id); if (el) el.textContent='—'; });
  }
})();