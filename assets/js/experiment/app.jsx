import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'

import '../../css/experiment/app.scss'

import ChartView from './components/ChartView'
import LogView from './components/LogView'

class App extends Component {
  render () {
    return (
      <div className='d-flex flex-column justify-content-between'>
        <div className='flex-fill'>
          <ChartView
            series={this.props.sensors}
            dataUrl={this.props.measurementsUrl}
            eventSourceUrl={this.props.eventSourceUrl}
          />
        </div>
        <div className='flex-fill'>
          <LogView
            dataUrl={this.props.logEntriesUrl}
            eventSourceUrl={this.props.eventSourceUrl}
          />
        </div>
      </div>
    )
  }
}

App.propTypes = {
  sensors: PropTypes.object.isRequired,
  measurementsUrl: PropTypes.string.isRequired,
  logEntriesUrl: PropTypes.string.isRequired,
  eventSourceUrl: PropTypes.string.isRequired
}

const options = window.APP_OPTIONS || {}

ReactDOM.render(
  <App {...options} />,
  document.getElementById('app')
)
