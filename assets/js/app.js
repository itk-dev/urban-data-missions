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

const series = {
  // field ↦ options
  humidity: {},
  noise: {},
  temperature: {},
  'fixture:sensor:001': {
    name: 'Hep-hey!'
  }
}

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
  const data = {
    date: new Date(measurement.measured_at),
    [measurement.sensor]: measurement.value
  }
  chart.addData(data)

  // console.log('addMeasurement', data)
}

let date = new Date()
const addMinutes = (date, minutes) => {
  return new Date(date.getTime() + minutes * 60 * 1000)
}

const timer = setInterval(() => {
  date = addMinutes(date, 60)
  addMeasurement({
    measured_at: date.toISOString(),
    sensor: getRandomKey(series),
    value: getRandomInt(-20, 30)
  })

  if (chart.getData().length > 20) {
    clearInterval(timer)
  }
}, 200)

if (typeof window.APP_CONFIG !== 'undefined') {
  if (window.APP_CONFIG.eventSourceUrl) {
    const eventSource = new EventSource(APP_CONFIG.eventSourceUrl)
    eventSource.onmessage = event => {
      const data = JSON.parse(event.data)
      const measurement = data.measurement
      addMeasurement(measurement)

      console.log(measurement)
      document.getElementById('data').innerHTML += JSON.stringify(measurement)
    }
  }
}

$(function () {
  $('[data-toggle="popover"]').popover({
      animation: true,
      placement: 'auto',
      html: true
  })
})

