/* eslint-env browser */
/* global APP_CONFIG */

import '../css/app.scss'
import('@fortawesome/fontawesome-free/js/all')

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

const getRandomInt = (min, max) => {
  min = Math.ceil(min)
  max = Math.floor(max)

  return Math.floor(Math.random() * (max - min)) + min // The maximum is exclusive and the minimum is inclusive
}

const getRandomValue = (values) => {
  return values[getRandomInt(0, values.length)]
}

const getRandomKey = (map) => {
  return getRandomValue(Object.keys(map))
}

const addMeasurement = (measurement) => {
  console.log('addMeasurement', measurement.sensor, measurement.sensor in series)
  if (measurement.sensor in series) {
    const data = {
      date: new Date(measurement.measured_at),
      [measurement.sensor]: measurement.value
    }
    chart.addData(data)

    // console.log('addMeasurement', data)
  }
}

if (options.eventSourceUrl) {
  const eventSource = new EventSource(options.eventSourceUrl)
  eventSource.onmessage = event => {
    const data = JSON.parse(event.data)
    const measurement = data.measurement
    addMeasurement(measurement)
  }
}
