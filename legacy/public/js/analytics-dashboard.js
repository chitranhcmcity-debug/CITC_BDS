(function () {
    'use strict';
    const data = window.AnalyticsDashboardData;
    if (!data || typeof Chart === 'undefined') return;

    const colors = ['#0d6efd','#16a34a','#f59e0b','#7c3aed','#ef4444','#06b6d4'];
    const grid = { color:'rgba(148,163,184,.18)' };
    const chart = (id, config) => {
        const canvas = document.getElementById(id);
        if (canvas) new Chart(canvas, config);
    };

    chart('analyticsDailyChart', {
        type:'line',
        data:{
            labels:data.daily.map(x=>x.label),
            datasets:[{label:'Lượt xem',data:data.daily.map(x=>x.views),borderColor:colors[0],backgroundColor:'rgba(13,110,253,.12)',fill:true,tension:.35,pointRadius:data.daily.length>31?0:3}]
        },
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid},x:{grid:{display:false}}}}
    });

    chart('analyticsMonthlyChart', {
        type:'bar',
        data:{labels:data.monthly.map(x=>x.label),datasets:[{label:'Lượt xem',data:data.monthly.map(x=>x.views),backgroundColor:'rgba(13,110,253,.78)',borderRadius:6}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid},x:{grid:{display:false}}}}
    });

    chart('analyticsActionsChart', {
        type:'bar',
        data:{
            labels:['View','Call','Chat','Save','Share'],
            datasets:[{label:'Sự kiện',data:[data.summary.views,data.summary.calls,data.summary.chats,data.summary.saves,data.summary.shares],backgroundColor:colors,borderRadius:7}]
        },
        options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,grid},y:{grid:{display:false}}}}
    });

    const sources = data.sources.length ? data.sources : [{label:'Chưa có dữ liệu',total:1}];
    chart('analyticsSourcesChart', {
        type:'doughnut',
        data:{labels:sources.map(x=>x.label),datasets:[{data:sources.map(x=>x.total),backgroundColor:colors,borderWidth:0}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'66%',plugins:{legend:{position:'bottom'}}}
    });
})();
