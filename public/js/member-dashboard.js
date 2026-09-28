(() => {
  'use strict';
  const source=window.DASHBOARD_DATA||{period:30,chart:[]}; let chart;
  const datasets=(rows)=>[
    ['views','Lượt xem','#2563eb'],['calls','Lượt gọi','#f59e0b'],['chats','Lượt chat','#06b6d4'],
    ['saves','Lượt lưu','#ef4444'],['shares','Lượt chia sẻ','#22c55e']
  ].map(([key,label,color])=>({label,data:rows.map(x=>x[key]),borderColor:color,backgroundColor:color,tension:.32,pointRadius:2,borderWidth:2}));
  function render(rows){const canvas=document.getElementById('dashboard-performance-chart');if(!canvas||!window.Chart)return;if(chart)chart.destroy();chart=new Chart(canvas,{type:'line',data:{labels:rows.map(x=>x.label),datasets:datasets(rows)},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false}}}}});}
  document.querySelectorAll('.chart-period').forEach(button=>button.addEventListener('click',async()=>{const period=button.dataset.period;button.disabled=true;try{const response=await fetch(`${window.DASHBOARD_ROOT}/nguoi-dung/dashboard/chart?period=${period}`,{headers:{Accept:'application/json'}});const payload=await response.json();if(!response.ok||!payload.success)throw new Error(payload.message||'Không thể tải biểu đồ.');document.querySelectorAll('.chart-period').forEach(x=>x.classList.toggle('active',x===button));render(payload.data);}catch(error){window.Swal?Swal.fire('Lỗi',error.message,'error'):alert(error.message);}finally{button.disabled=false;}}));
  document.querySelectorAll('.dashboard-delete-form').forEach(form=>form.addEventListener('submit',event=>{if(!window.Swal)return;if(form.dataset.confirmed==='1')return;event.preventDefault();Swal.fire({title:'Xóa tin đăng?',text:'Hành động này không thể hoàn tác.',icon:'warning',showCancelButton:true,confirmButtonText:'Xóa',cancelButtonText:'Hủy',confirmButtonColor:'#dc3545'}).then(result=>{if(result.isConfirmed){form.dataset.confirmed='1';form.submit();}});}));
  render(source.chart||[]);
})();
