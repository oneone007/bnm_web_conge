// Data for charts
const monthlyStats = [5000, 10000, -5000, 15000, 20000, 12000, -8000];
const dataGraphSales = {
  labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
  datasets: [
    {
      label: 'Product Sales',
      data: [20000, 15000, 10000, 30000, 25000, 40000, 35000],
      borderColor: 'pink',
      borderWidth: 2,
    },
    {
      label: 'Marketing Sales',
      data: [15000, 20000, 30000, 25000, 40000, 35000, 30000],
      borderColor: 'blue',
      borderWidth: 2,
    },
    {
      label: 'Total Earnings',
      data: [30000, 35000, 40000, 50000, 45000, 55000, 60000],
      borderColor: 'gold',
      borderWidth: 2,
    },
  ],
};

// Data Graph Chart
new Chart(document.getElementById('dataChart'), {
  type: 'line',
  data: dataGraphSales,
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: true,
        position: 'top',
      },
    },
  },
});

// Monthly Stats Chart
new Chart(document.getElementById('monthlyChart'), {
  type: 'bar',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
    datasets: [
      {
        label: 'Monthly Stats',
        data: monthlyStats,
        backgroundColor: 'blue',
      },
    ],
  },
  options: {
    responsive: true,
  },
});
