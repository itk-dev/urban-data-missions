/* global fetch, EventSource */
import React, { Component } from 'react'
import PropTypes from 'prop-types'

class LogView extends Component {
  constructor (props) {
    super(props)
    this.state = {
      entries: []
    }
  }

  componentDidMount () {
    fetch(this.props.dataUrl)
      .then((response) => {
        return response.json()
      })
      .then(entries => {
        entries.reverse()
        this.setState({ entries: entries }, () => {
          const eventSource = new EventSource(this.props.eventSourceUrl)
          eventSource.onmessage = event => {
            const data = JSON.parse(event.data)
            if (data.log_entry) {
              this.setState({ entries: [data.log_entry, ...this.state.entries] })
            }
          }
        })
      })
  }

  render () {
    return (
      <fieldset className='log-view'>
        <legend>Log</legend>

        <div className='log-view-content'>
          {this.state.entries.map((entry, index) => (
            <div className={`log-entry log-entry-${entry.type}`} key={'log-entry-' + index}>
              <span className={`badge badge-${entry.type}`}>{entry.type}</span>
              <div className='logged-at'>{entry.loggedAt}</div>
              {entry.sensor && <div className='sensor'>{entry.sensor.id}</div>}
              {entry.content}
            </div>
          ))}
        </div>
      </fieldset>
    )
  }
}

LogView.propTypes = {
  dataUrl: PropTypes.string.isRequired,
  eventSourceUrl: PropTypes.string.isRequired
}

export default LogView
