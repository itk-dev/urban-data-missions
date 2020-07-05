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
      message: null
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
          const chart = this.getChart()
          chart.closeAllPopups()
          const popup = chart.openPopup('Add annotation …')
          popup.left = chartEvent.svgPoint.x + 15
          popup.top = chartEvent.svgPoint.y + 15
          popup.title = ''
          popup.content = `<button class="btn btn-success btn-sm btn-add-log-entry">
 <span className='fas fa-plus'></span>
${Translator.trans('Add log entry')}
</button>
`
          const button = popup.elements.content.querySelector('button')
          const dataContext = chartEvent.target.dataItem.dataContext
          button.addEventListener('click', function (event) {
            chart.closeAllPopups()
            messenger.emit('addLogEntry', {
              mission: mission,
              loggedAt: dataContext.date.toISOString(),
              type: 'measurement',
              measurement: dataContext.measurement
            })
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

    fetch(this.props.dataUrl, { headers: { accept: 'application/ld+json' } })
      .then((response) => {
        return response.json()
      })
      .then(data => {
        data['hydra:member'].forEach(this.addMeasurement)

        this.props.messenger.on('message', data => {
          if (data.measurement) {
            this.addMeasurement(data.measurement)
          }
        })
      })

    this.props.messenger.on('logEntryCreated', (data) => {
      const entry = data.result
      if (entry && entry.measurement) {
        this.setState({
          message: {
            content: Translator.trans('Log entry created: {content}', { content: entry.content }),
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
