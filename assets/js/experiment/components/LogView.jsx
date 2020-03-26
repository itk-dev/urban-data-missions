/* global fetch, EventSource */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Button from 'react-bootstrap/Button'

class LogView extends Component {
  constructor (props) {
    super(props)
    this.state = {
      entries: []
    }
  }

  addEntry (logEntry) {
    this.setState({ entries: [logEntry, ...this.state.entries] })
  }

  componentDidMount () {
    fetch(this.props.dataUrl, { headers: { accept: 'application/json' } })
      .then((response) => {
        return response.json()
      })
      .then(entries => {
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
      <section className='log-view'>
        <header className='d-flex justify-content-between'>
          <div><h1>Log</h1></div>
          <div className='log-action'>
            {this.props.handleAddLogEntry && <Button className='btn-add-annotation' onClick={this.props.handleAddLogEntry}>Add log entry</Button>}
          </div>
        </header>

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
      </section>
    )
  }
}

LogView.propTypes = {
  dataUrl: PropTypes.string.isRequired,
  eventSourceUrl: PropTypes.string.isRequired,
  onHandleAddLogEntry: PropTypes.func
}

export default LogView
