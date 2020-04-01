/* global fetch, EventSource */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Chart from '../../../js/components/Chart'

class ChartView extends Component {
  // @see https://www.amcharts.com/docs/v4/getting-started/integrations/using-react/
  componentDidMount () {
    const handleAddLogEntry = this.props.onHandleAddLogEntry || (() => {})
    const chart = new Chart({
      series: this.props.series,
      bullet: {
        onHit: function (chartEvent) {
          const chart = this.getChart()
          chart.closeAllPopups()
          const popup = chart.openPopup('Add annotation …')
          popup.left = chartEvent.svgPoint.x + 15
          popup.top = chartEvent.svgPoint.y + 15
          popup.title = ''
          popup.content = `<button class="btn btn-success btn-sm btn-add-log-entry">
 <span className='fas fa-plus'></span>
Add log entry
</button>
`
          const button = popup.elements.content.querySelector('button')
          const dataContext = chartEvent.target.dataItem.dataContext
          button.addEventListener('click', function (event) {
            chart.closeAllPopups()
            if (handleAddLogEntry) {
              handleAddLogEntry({
                loggedAt: dataContext.date.toISOString(),
                type: 'sensor',
                sensor: decodeURIComponent(dataContext.measurement.sensor['@id'])
              })
            }
          })
        }
      },
      legend: true,
      cursor: true,
      scrollbars: true,
      export: true
    })

    this.chart = chart

    fetch(this.props.dataUrl, { headers: { accept: 'application/ld+json' } })
      .then((response) => {
        return response.json()
      })
      .then(data => {
        data['hydra:member'].forEach(this.addMeasurement)

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
    ;;; console.log('addMeasurement', measurement)
    const series = measurement.sensor.id
    if (series !== null && series in this.props.series) {
      const data = {
        date: new Date(measurement.measuredAt),
        [series]: measurement.value,
        measurement: measurement
      }

      this.chart.addData(data)
    }
  }

  getChart () {
    return this.chart.getChart()
  }

  handleAddLogEntry = () => {
    const logEntry = {
      loggedAt: (new Date('1975-05-23')).toISOString(),
      sensor: '/api/sensors/device:001:temperature'
    }
    this.props.onHandleAddLogEntry && this.props.onHandleAddLogEntry(logEntry)
  }

  render () {
    return (
      <section className='chart-view'>
        <header className='d-flex justify-content-between'>
          <div><h1>Chart</h1></div>
        </header>

        <div className='chart-view-content'>
          <div id='chart' />
        </div>
      </section>
    )
  }
}

ChartView.propTypes = {
  series: PropTypes.object.isRequired,
  dataUrl: PropTypes.string.isRequired,
  eventSourceUrl: PropTypes.string.isRequired,
  onHandleAddLogEntry: PropTypes.func
}

export default ChartView
