/* Import this components styling */
import './mission.scss'

import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'

import ChartView from './ChartView'
import LogView from './LogView'
import LogEntry from './LogEntry'
import Messenger from './Messenger'

// import '@fortawesome/fontawesome-free/js/all'
/* Import FontAwesome icons */
import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faMapMarkerAlt } from '@fortawesome/free-solid-svg-icons/faMapMarkerAlt'
import { faPlus } from '@fortawesome/free-solid-svg-icons/faPlus'
import { faTimesCircle } from '@fortawesome/free-solid-svg-icons/faTimesCircle'
import { faInfoCircle } from '@fortawesome/free-solid-svg-icons/faInfoCircle'
import { faEdit } from '@fortawesome/free-solid-svg-icons/faEdit'

library.add(faMapMarkerAlt, faPlus, faInfoCircle, faTimesCircle, faEdit)
dom.watch()

const $ = require('jquery')
require('popper.js')
require('bootstrap')

const chartExports = {}

const registerChartExport = (type, handler) => {
  chartExports[type] = handler
}

const exportChart = (type, format, options) => {
  if (chartExports[type]) {
    chartExports[type](format, options)
  }
}

$('[data-export="chart"][data-type][data-format]').on('click', function () {
  let { type, format, options } = this.dataset
  options = JSON.parse(options || '{}')
  options.filename = (options.filename || 'mission') + '-' + (new Date().getTime())
  exportChart(type, format, options)
})

class App extends Component {
  constructor (props) {
    super(props)
    this.messenger = new Messenger({
      eventSourceUrl: this.props.eventSourceUrl
    })
  }

  render () {
    return (
      <>
        <div className='d-flex flex-column justify-content-between'>
          <div className='flex-fill mb-5'>
            <ChartView
              mission={this.props.mission}
              series={this.props.sensors}
              dataUrl={this.props.measurementsUrl}
              messenger={this.messenger}
              registerChartExport={this.props.registerChartExport}
              options={this.props.options}
            />
          </div>
          <div className='flex-fill mb-5'>

            <LogEntry
              postUrl={this.props.logEntryPostUrl}
              messenger={this.messenger}
            />

            <LogView
              mission={this.props.mission}
              dataUrl={this.props.logEntriesUrl}
              messenger={this.messenger}
            />
          </div>
        </div>
      </>
    )
  }
}

App.propTypes = {
  mission: PropTypes.object.isRequired,
  sensors: PropTypes.object.isRequired,
  measurementsUrl: PropTypes.string.isRequired,
  logEntriesUrl: PropTypes.string.isRequired,
  eventSourceUrl: PropTypes.string.isRequired,
  logEntryPostUrl: PropTypes.string.isRequired,
  registerChartExport: PropTypes.func
}

const el = document.getElementById('app')
const options = JSON.parse(el.dataset.options || '{}')
options.mission = JSON.parse(options.mission || '{}')
options.registerChartExport = registerChartExport

ReactDOM.render(
  <App {...options} />,
  document.getElementById('app')
)
