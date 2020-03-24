/* global fetch, EventSource */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Chart from './../../components/Chart'

class ChartView extends Component {
  // @see https://www.amcharts.com/docs/v4/getting-started/integrations/using-react/
  componentDidMount () {
    const chart = new Chart({
      series: this.props.series,
      legend: true,
      cursor: true,
      scrollbars: true
    })

    this.chart = chart

    fetch(this.props.dataUrl)
      .then((response) => {
        return response.json()
      })
      .then(measurements => {
        measurements.forEach(this.addMeasurement)

        const eventSource = new EventSource(this.props.eventSourceUrl)
        eventSource.onmessage = event => {
          const data = JSON.parse(event.data)
          if (data.measurement) {
            this.addMeasurement(data.measurement)
          }
        }
      })
  }

  addMeasurement = (measurement) => {
    if (measurement.sensor in this.props.series) {
      const data = {
        date: new Date(measurement.measuredAt),
        [measurement.sensor]: measurement.value
      }
      this.chart.addData(data)
    }
  }

  render () {
    return (
      <fieldset className='chart-view'>
        <legend>Chart</legend>

        <div id='chart' />
      </fieldset>
    )
  }
}

ChartView.propTypes = {
  series: PropTypes.object.isRequired,
  dataUrl: PropTypes.string.isRequired,
  eventSourceUrl: PropTypes.string.isRequired
}

export default ChartView
