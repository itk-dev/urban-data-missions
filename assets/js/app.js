/* eslint-env browser */

import '../css/app.scss'
import '@fortawesome/fontawesome-free/js/all'

import Chart from './components/Chart'

require('typeface-roboto')
require('jquery')
require('popper.js')
require('bootstrap')

import $ from 'jquery'

const options = window.APP_OPTIONS || {}

const series = options.sensors

const chart = new Chart({
  series: series,
  legend: true,
  cursor: true
})

/**
 * Add measurement to graph.
 */
const addMeasurement = (measurement) => {
  if (measurement.sensor in series) {
    const data = {
      date: new Date(measurement.measuredAt),
      [measurement.sensor]: measurement.value
    }
    chart.addData(data)
  }
}

// Get existing data
if (options.measurementsUrl) {
  fetch(options.measurementsUrl)
    .then((response) => {
      return response.json()
    })
    .then(measurements => {
      measurements.forEach(measurement => addMeasurement(measurement))

      // Subscribe to new data.
      if (options.eventSourceUrl) {
        const eventSource = new EventSource(options.eventSourceUrl)
        eventSource.onmessage = event => {
          const data = JSON.parse(event.data)
          if (data.measurement) {
            addMeasurement(data.measurement)
          }
        }
      }
    })
}

if (options.logEntriesUrl) {
  document.getElementById('log-entries').innerHTML = 'Loading log entries …'
  fetch(options.logEntriesUrl)
    .then((response) => {
      return response.json()
    })
    .then(entries => {
      document.getElementById('log-entries').innerHTML = JSON.stringify(entries, null, 2)
    })
}
