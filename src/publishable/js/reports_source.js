import axios from 'axios';
import _ from 'lodash/object';
import randomColor from 'random-color';
import roundTo from 'round-to';
import toInteger from 'to-integer';

const getColor = () => randomColor(0.5, 0.7).alpha(0.5).rgbaString();
const formatData = query => query.map(item => _.values(item));

const initBar = ({ id, name, type, query, fields }) => {
  // If the query is a query database, you need to format it
  if (typeof query === 'object') {
    query = formatData(query);
  }

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
  // If the query is a query database, you need to format it
  if (typeof query === 'object') {
    query = formatData(query);
  }

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
  // If the query is a query database, you need to format it
  if (typeof query === 'object') {
    query = formatData(query);
  }

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
            maxRotation: 180,
          },
        }]
      },
    },
  });
};

const dateRange = (id) => {
  $(`input[name="${id}"]`).daterangepicker({
    showDropdowns: true,
    opens: 'center',
    locale: {
      format: 'YYYY-MM-DD',
    }
  }, function (start, end, label) {
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
  });
};

const filterChart = (event) => {
  event.preventDefault();
  const form = event.target;
  const spin = form.querySelector('.spin-loader');
  const button = form.querySelector('button');
  const range = form.querySelector('.input-range').value;
  const uuid = form.getAttribute('uuid');

  button.disabled = true;
  spin.classList.remove('hidden');

  axios.post('/admin/reports/filter', {
    uuid,
    range,
  })
    .then(response => {
      if (response.data) { drawCharts(response.data); }
    })
    .catch(error => console.log(error))
    .then(() => {
      button.disabled = false;
      spin.classList.add('hidden');
    });

};

const drawCharts = data => {
  switch (data.type) {
    case 'horizontalBar':
    case 'bar':
      initBar(data);
      break;
    case 'doughnut':
      initDonut(data);
      break;
    case 'line':
      initArea(data);
      break;

    default:
      console.log('Unsupported');
  }
};

const initCharts = () => {
  const chartsFilters = document.querySelectorAll('.filter-chart');
  chartsFilters.forEach(item => {
    dateRange(item.querySelector('.input-range').name);
    item.addEventListener('submit', filterChart);
  });

  chartData.forEach(item => drawCharts(item));
};

initCharts();
