/* global fetch */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Button from 'react-bootstrap/Button'
import Badge from 'react-bootstrap/Badge'
import Alert from 'react-bootstrap/Alert'
import Messenger from './Messenger'
import Translator from '../translations'

class LogView extends Component {
  constructor (props) {
    super(props)
    this.state = {
      message: null,
      entries: [],
      filter: {
        // Display order
        names: [
          Translator.trans('alert'),
          Translator.trans('measurement'),
          Translator.trans('system'),
          Translator.trans('user')
        ],
        // Actual values
        values: {
          alert: true,
          measurement: true,
          system: true,
          user: true
        }
      }
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
          this.props.messenger.on('message', data => {
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
          })
        })
      })

    this.props.messenger.on('logEntryCreated', (data) => {
      const entry = data.result
      if (entry && !entry.measurement) {
        this.setState(
          {
            message: {
              content: Translator.trans('Log entry created: %content%', { content: entry.content }),
              type: 'success'
            }
          }
        )
      }
    })
  }

  showMeasurementLogEntry = (entry) => {
    this.props.messenger.emit('showMeasurementLogEntry', entry)
  }

  handleAddLogEntry = () => {
    const logEntry = {
      mission: this.props.mission,
      loggedAt: (new Date()).toISOString()
    }
    this.props.messenger.emit('addLogEntry', logEntry)
  }

  applyFilter = (entry) => {
    return this.state.filter.values[entry.type]
  }

  toggleFilter = (name) => {
    const filter = this.state.filter

    // At least one name must always be selected
    const values = { ...filter.values }
    values[name] = !values[name]
    if (Object.values(values).filter(value => value).length < 1) {
      return
    }

    filter.values = values
    this.setState({ filter: filter })
  }

  render () {
    const renderFilter = (name) => {
      const active = this.state.filter.values[name]

      return (
        <Badge
          key={`filter-${name}`} pill
          className={`filter-${name} ${active ? 'active' : ''} badge-log-entry-type badge-log-entry-type-${name} mr-1`}
          onClick={() => this.toggleFilter(name)}
        >{name}
        </Badge>
      )
    }

    const entries = this.state.entries.filter(this.applyFilter)

    return (
      <section className='log-view'>
        <header className='row mt-3'>
          <div className='col-auto'>
            <h2>{Translator.trans('Mission log')}</h2>
          </div>
          <div className='col'>
            <Button variant='success' className='btn-sm rounded-circle btn-add-log-entry' onClick={this.handleAddLogEntry}>
              <span className='fas fa-plus' />
            </Button>
          </div>
        </header>

        <div className='row mb-3'>
          <div className='col-auto'>
            <span className='text-muted'>{Translator.trans('Filter log types')}</span>
          </div>
          <div className='col'>
            <div className='mission-log-filter-types btn-group btn-group-toggle'>
              {this.state.filter.names.map(renderFilter)}
            </div>
          </div>
        </div>

        {this.state.message && <Alert dismissible onClose={() => this.setState({ message: null })} variant={this.state.message.type}>{this.state.message.content}</Alert>}

        <div className='row'>
          <div className='col'>
            <div className='log-view-content'>
              {entries.length === 0
                ? <Alert variant='warning'>{Translator.trans('No log entries')}</Alert>
                : entries.map((entry, index) => (
                  <article className={`row mb-3 log-entry log-entry-${entry.type}`} key={'log-entry-' + index}>
                    <div className='col-auto'>
                      <p className='small mb-0 text-primary-faded pt-1'>{entry.loggedAt}</p>
                      <span className={`badge badge-pill badge-sm badge-log-entry-type badge-log-entry-type-${entry.type}`}>{entry.type}</span>
                    </div>
                    <div className='col'>
                      <p className='mb-0'>{entry.content}</p>
                      <p className='text-muted small'>{entry.measurement && <span className='measurement ' title={`Sensor id: ${entry.measurement.sensor.id}`} onClick={() => this.showMeasurementLogEntry(entry)}>{entry.measurement.sensorName}: {entry.measurement.value}</span>}
                      </p>
                    </div>
                  </article>
                ))}
            </div>
          </div>
        </div>
      </section>
    )
  }
}

LogView.propTypes = {
  mission: PropTypes.object.isRequired,
  dataUrl: PropTypes.string.isRequired,
  messenger: PropTypes.instanceOf(Messenger).isRequired
}

export default LogView
