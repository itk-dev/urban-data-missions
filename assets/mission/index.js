/* Import base styling */
import '../base.scss'

/* Import this components styling */
import './mission.scss'

/* Require roboto typeface */
require('typeface-roboto')
require('jquery')
require('popper.js')
require('bootstrap')

import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'

import '@fortawesome/fontawesome-free/js/all'

const L = require('leaflet')

const elMap = document.getElementById('map')
const mapOptions = JSON.parse(elMap.dataset.options)
const missions = mapOptions.missions || []

const map = L.map(elMap).setView([0, 0], 13)

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
}).addTo(map)

const markerIcon = L.divIcon({ className: 'map-marker-icon' })

const bounds = L.latLngBounds()
for (const mission of missions) {
  const marker = L.marker([mission.latitude, mission.longitude], {
    icon: markerIcon
  }).addTo(map)
  bounds.extend(marker.getLatLng())

  const showUrl = mapOptions.show_url_template.replace('%id%', mission.id)
  // @TODO: Design
  marker.bindPopup(`<div class="mission-title">${mission.title}</div> <a class="btn btn-secondary" href="${showUrl}">Show</a>`)
}

map.fitBounds(bounds)


import ChartView from './ChartView'
import LogView from './LogView'
import LogEntry from './LogEntry'

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
