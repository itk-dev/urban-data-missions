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
    fetch(this.props.dataUrl, { headers: { accept: 'application/ld+json' } })
      .then((response) => {
        return response.json()
      })
      .then(data => {
        this.setState({ entries: data['hydra:member'] }, () => {
          const eventSource = new EventSource(this.props.eventSourceUrl)
          eventSource.onmessage = event => {
            const data = JSON.parse(event.data)
            if (data.log_entry) {
              const entries = [data.log_entry, ...this.state.entries]
              // Sort by loggedAt desc
              entries.sort((a, b) => {
                if (a.loggedAt < b.loggedAt) {
                  return 1
                } else if (a.loggedAt > b.loggedAt) {
                  return -1
                }
                return 0
              })
              this.setState({ entries: entries })
            }
          }
        })
      })
  }

  handleAddLogEntry = () => {
    const logEntry = {
      loggedAt: (new Date()).toISOString()
    }
    this.props.onHandleAddLogEntry && this.props.onHandleAddLogEntry(logEntry)
  }

  render () {
    return (
      <section className='log-view'>
        <header className='row my-3'>
          <div className='col-auto'>
            <h2>Mission log</h2>
          </div>
          <div className='col'>
            {this.props.onHandleAddLogEntry &&
              <Button variant='success' className='btn-sm rounded-circle btn-add-log-entry' onClick={this.handleAddLogEntry}>
                <span className='fas fa-plus' />
              </Button>}
          </div>
        </header>

        <div className='log-view-content'>
          {this.state.entries.map((entry, index) => (
            <article className={`row mb-3 log-entry log-entry-${entry.type}`} key={'log-entry-' + index}>
              <div className='col-auto'>
                <p className='small mb-0 text-primary-faded pt-1'>{entry.loggedAt}</p>
                <span className={`badge badge-pill badge-sm badge-log-entry-type badge-log-entry-type-${entry.type}`}>{entry.type}</span>
              </div>
              <div className='col'>
                <p>{entry.content}</p>
                {/* @TODO: Design */}
                {entry.sensor && <div className='sensor'>{entry.sensor.id}</div>}
              </div>
            </article>
          ))}
        </div>
      </section>
    )
  }
}

LogView.propTypes = {
  dataUrl: PropTypes.string.isRequired,
  eventSourceUrl: PropTypes.string.isRequired,
  onHandleAddLogEntry: PropTypes.func.isRequired
}

export default LogView
