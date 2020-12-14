/* global fetch */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Chart from './Chart'
import Messenger from './Messenger'
import Alert from 'react-bootstrap/Alert'
import Translator from '../translations'

class ChartView extends Component {
  constructor (props) {
    super(props)
    this.state = {
      message: null,
      loadingDataMessage: null
    }
  }

  // @see https://www.amcharts.com/docs/v4/getting-started/integrations/using-react/
  componentDidMount () {
    const messenger = this.props.messenger
    const mission = this.props.mission
    const chart = new Chart({
      series: this.props.series,
      bullet: {
        onHit: function (chartEvent) {
          const dataContext = chartEvent.target.dataItem.dataContext
          messenger.emit('addLogEntry', {
            mission: mission,
            loggedAt: dataContext.date.toISOString(),
            type: 'measurement',
            measurement: dataContext.measurement
          })
        }
      },
      legend: true,
      cursor: true,
      scrollbars: true
    })

    this.chart = chart

    if (typeof this.props.registerChartExport === 'function') {
      this.props.registerChartExport('image', this.chart.exportImage.bind(this.chart))
    }

    this.props.messenger.on('message', data => {
      if (data.measurement) {
        this.addMeasurement(data.measurement)
      }
    })

    const maxNumberOfMeasurementsToLoad = this.props.options.maxNumberOfMeasurementsToLoad ?? 3000
    const initialDataWindowSize = this.props.options.initialDataWindowSize ?? 4 * 60 * 60

    let loadedData = []
    const loadData = (url) => {
      this.setState({
        loadingDataMessage: `Loading measurements (${loadedData.length} loaded)`
      })
      fetch(url, { headers: { accept: 'application/ld+json' } })
        .then((response) => {
          return response.json()
        })
        .then(data => {
          loadedData = loadedData.concat(data['hydra:member'])

          const nextUrl = data['hydra:view']['hydra:next']
          if (nextUrl && loadedData.length <= maxNumberOfMeasurementsToLoad) {
            loadData(nextUrl)
          } else {
            this.setState({
              loadingDataMessage: null
            })

            this.chart.getChart().events.once('validated', () => {
              // Zoom to last 4 hours.
              const now = (new Date()).getTime()
              const start = now - 1000 * initialDataWindowSize
              const end = now

              this.chart.zoomToValues(
                start, end
              )
            })

            this.addMeasurements(loadedData)
          }
        })
    }
    loadData(this.props.dataUrl)

    this.props.messenger.on('logEntryCreated', (data) => {
      const entry = data.result
      if (entry && entry.measurement) {
        this.setState({
          message: {
            content: Translator.trans('Log entry created: %content%', { content: entry.content }),
            type: 'success'
          }
        })
      }
    })

    this.props.messenger.on('showMeasurementLogEntry', (logEntry) => {
      console.log('ChartView.showMeasurementLogEntry', logEntry)
    })
  }

  addMeasurement = (measurement) => {
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

  /**
   * Add measurements to chart
   *
   * Sorts data on chart.
   */
  addMeasurements = (measurements) => {
    const data = this.chart.getData()
    measurements.forEach(measurement => {
      const series = measurement.sensor.id
      if (series !== null && series in this.props.series) {
        data.push({
          date: new Date(measurement.measuredAt),
          [series]: measurement.value,
          measurement: measurement
        })
      }
    })
    data.sort((a, b) => a.date - b.date)
    this.chart.setData(data)
  }

  getChart () {
    return this.chart.getChart()
  }

  render () {
    return (
      <section className='chart-view'>
        <header className='d-flex justify-content-between'>
          <div><h2>Chart</h2></div>
          <div>
            {this.state.message && <Alert dismissible onClose={() => this.setState({ message: null })} variant={this.state.message.type}>{this.state.message.content}</Alert>}
            {this.state.loadingDataMessage && <Alert variant='info'>{this.state.loadingDataMessage}</Alert>}
          </div>
        </header>

        <div className='chart-view-content'>
          <div id='chart' className='chart' />
        </div>
      </section>
    )
  }
}

ChartView.propTypes = {
  mission: PropTypes.object.isRequired,
  series: PropTypes.object.isRequired,
  dataUrl: PropTypes.string.isRequired,
  messenger: PropTypes.instanceOf(Messenger).isRequired,
  registerChartExport: PropTypes.func
}

export default ChartView
