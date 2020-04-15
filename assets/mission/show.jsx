/* Import this components styling */
import './mission.scss'

import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'

import ChartView from './ChartView'
import LogView from './LogView'
import LogEntry from './LogEntry'
import Messenger from './Messenger'

import '@fortawesome/fontawesome-free/js/all'

require('jquery')
require('popper.js')
require('bootstrap')

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
          <div className='flex-fill'>
            <ChartView
              mission={this.props.mission}
              series={this.props.sensors}
              dataUrl={this.props.measurementsUrl}
              messenger={this.messenger}
            />
          </div>
          <div className='flex-fill'>

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
  logEntryPostUrl: PropTypes.string.isRequired
}

const el = document.getElementById('app')
const options = JSON.parse(el.dataset.options || '{}')
options.mission = JSON.parse(options.mission || '{}')

ReactDOM.render(
  <App {...options} />,
  document.getElementById('app')
)
