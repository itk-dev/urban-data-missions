/* Import this components styling */
import './mission.scss'

import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'

import ChartView from './ChartView'
import LogView from './LogView'
import LogEntry from './LogEntry'

import '@fortawesome/fontawesome-free/js/all'

require('jquery')
require('popper.js')
require('bootstrap')

class App extends Component {
  constructor (props) {
    super(props)
    this.state = {
      logEntry: null
    }
  }

  handleAddLogEntry = (data) => {
    const logEntry = {
      ...{
        // Required and default values
        mission: this.props.mission['@id'],
        loggedAt: (new Date()).toISOString()
      },
      ...data
    }
    console.log('App.handleAddLogEntry', logEntry)
    this.setState({ logEntry: logEntry })
  }

  handleLogEntryAdded = (logEntry) => {
    console.log('App.handleLogEntryAdded', logEntry)
    this.setState({ logEntry: null })
  }

  render () {
    return (
      <>
        <div className='d-flex flex-column justify-content-between'>
          <div className='flex-fill'>
            <ChartView
              series={this.props.sensors}
              dataUrl={this.props.measurementsUrl}
              eventSourceUrl={this.props.eventSourceUrl}
              onHandleAddLogEntry={this.handleAddLogEntry}
            />
          </div>
          <div className='flex-fill'>

            <LogEntry
              mission={this.props.mission} postUrl={this.props.logEntryPostUrl} logEntry={this.state.logEntry}
              onHandleLogEntryAdded={this.handleLogEntryAdded}
            />

            <LogView
              dataUrl={this.props.logEntriesUrl}
              eventSourceUrl={this.props.eventSourceUrl}
              onHandleAddLogEntry={this.handleAddLogEntry}
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
