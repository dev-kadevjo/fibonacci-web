import toInteger from 'to-integer';
import randomColor from 'random-color';
import roundTo from 'round-to';

const getColor = () => randomColor(0.5, 0.7).alpha(0.5).rgbaString();

const initBar = ({ id, name, type, query, fields }) => {
  const backgroundColor = query.map(() => getColor());
  const ctx = document.getElementById(`Chart${id}`).getContext('2d');
  const myChart = new Chart(ctx, {
    type,
    data: {
      labels: query.map(item => item[0]),
      datasets: fields.split(',').map((item, index) => ({
        label: item,
        data: query.map(subitem => subitem[index + 1]),
        backgroundColor: getColor(),
      })),
    },
    options: {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          }
        }],
        xAxes: [{
          ticks: {
            beginAtZero: true,
            maxRotation: 180,
          }
        }]
      },
      title: {
        display: true,
        text: name,
      },
    },
  });
};

const initDonut = ({ id, name, query, fields }) => {
  const backgroundColor = query.map(() => getColor());
  const ctx = document.getElementById(`Chart${id}`).getContext('2d');
  const myChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: query.map(item => item[0]),
      datasets: fields.split(',').map((item, index) => ({
        label: item,
        data: query.map(subitem => subitem[index + 1]),
        backgroundColor,
      })),
    },
    options: {
      responsive: true,
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: name,
      },
      animation: {
        animateScale: true,
        animateRotate: true
      },
      tooltips: {
        displayColors: false,
        callbacks: {
          label: function (tooltipItem, data) {
            const n = data.datasets[tooltipItem.datasetIndex].data.reduce((valorAnterior, valorActual) => {
              return toInteger(valorAnterior) + toInteger(valorActual);
            }, 0);
            const newVal = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
            const percentage = roundTo((newVal / n) * 100, 2);

            const newLabel = [
              data.datasets[tooltipItem.datasetIndex].label,
              '',
              `${data.labels[tooltipItem.index]}: ${percentage}%`,
            ];
            return newLabel;
          }
        }
      }
    },
  });
};

const initArea = ({ id, name, query, fields }) => {
  const ctx = document.getElementById(`Chart${id}`).getContext('2d');
  const myChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: query.map(item => item[0]),
      datasets: fields.split(',').map((item, index) => ({
        label: item,
        data: query.map(subitem => subitem[index + 1]),
        backgroundColor: getColor(),
      })),
    },
    options: {
      title: {
        text: name,
        display: true,
      },
      animation: {
        animateScale: true,
        animateRotate: true,
      },
      responsive: true,
      maintainAspectRatio: false,
      spanGaps: false,
      elements: {
        line: {
          tension: 0.000001,
        },
      },
      plugins: {
        filler: {
          propagate: false,
        },
      },
      scales: {
        xAxes: [{
          ticks: {
            autoSkip: false,
            maxRotation: 0,
          },
        }]
      },
    },
  });
};

const initCharts = () => {
  chartData.forEach(item => {
    if (item.source !== 'db') {
      switch (item.type) {
        case 'horizontalBar':
        case 'bar':
          initBar(item);
          break;
        case 'doughnut':
          initDonut(item);
          break;
        case 'line':
          initArea(item);
          break;

        default:
          console.log('Unsupported');
      }
    }
  });
};

initCharts();
