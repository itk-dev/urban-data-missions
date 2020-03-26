import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'

import '../../css/experiment/app.scss'

import ChartView from './components/ChartView'
import LogView from './components/LogView'
import LogEntry from './components/LogEntry'

class App extends Component {
  handleAddLogEntry = (event) => {
    console.log('App.handleAddLogEntry')
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

            <LogEntry experiment={this.props.experiment} postUrl={this.props.logEntryPostUrl} />

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
  experiment: PropTypes.object.isRequired,
  sensors: PropTypes.object.isRequired,
  measurementsUrl: PropTypes.string.isRequired,
  logEntriesUrl: PropTypes.string.isRequired,
  eventSourceUrl: PropTypes.string.isRequired,
  logEntryPostUrl: PropTypes.string.isRequired
}

const options = window.APP_OPTIONS || {}
options.experiment = JSON.parse(options.experiment)

ReactDOM.render(
  <App {...options} />,
  document.getElementById('app')
)
