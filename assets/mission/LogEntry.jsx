/* global fetch */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Modal from 'react-bootstrap/Modal'
import Button from 'react-bootstrap/Button'
import Form from 'react-bootstrap/Form'
import Alert from 'react-bootstrap/Alert'
import Messenger from './Messenger'

class LogEntry extends Component {
  constructor (props) {
    super(props)
    this.state = this.getInitialState()
  }

  getInitialState = () => ({
    logEntry: null,
    show: false,
    isSubmitting: false,
    violations: {},
    message: null,
    messageType: null
  })

  componentDidMount () {
    this.props.messenger.on('addLogEntry', (logEntry) => this.setState({ logEntry: logEntry }))
  }

  resetState = () => {
    this.setState(this.getInitialState())
  }

  handleClose = () => {
    this.setState(this.getInitialState())
  }

  handleChange = (event) => {
    const logEntry = this.state.logEntry || {}
    this.setState({ logEntry: { ...logEntry, ...{ [event.target.name]: event.target.value } } })
  }

  handleSubmit = () => {
    this.setState({ isSubmitting: true }, () => {
      const logEntry = this.state.logEntry
      const data = {
        mission: logEntry.mission['@id'],
        content: logEntry.content,
        loggedAt: logEntry.loggedAt
      }
      if (logEntry.measurement) {
        data.measurement = logEntry.measurement['@id']
      }
      fetch(this.props.postUrl, {
        method: 'POST',
        headers: {
          'content-type': 'application/json'
        },
        body: JSON.stringify(data)
      })
        .then((response) => {
          this.setState({ isSubmitting: false })
          if (!response.ok) {
            response.json().then((data) => {
              if (data.violations) {
                const violations = {}
                for (const { propertyPath, message } of data.violations) {
                  violations[propertyPath] = message
                }
                this.setState({ violations: violations })
              } else {
                this.setState({
                  message: 'Error creating log entry',
                  messageType: 'danger'
                })
              }
            })
          } else {
            response.json().then((data) => {
              this.props.messenger.emit('logEntryCreated', {
                result: data,
                logEntry: logEntry
              })
              this.resetState()
              this.handleClose()
            })
          }
        })
        .catch((error) => {
          console.error('Error:', error)
        })
    })
  }

  render () {
    const form = (
      <Form onSubmit={this.handleSubmit}>
        {this.state.message && <Alert variant={this.state.messageType}>{this.state.message}</Alert>}
        <Form.Group controlId='formContent'>
          <Form.Label>Content</Form.Label>
          <Form.Control as='textarea' name='content' className={{ 'is-invalid': this.state.violations.content }} placeholder='Enter a log entry' value={this.state.logEntry?.content} onChange={this.handleChange} />
          {this.state.violations.content && <div className='invalid-feedback'>{this.state.violations.content}</div>}
        </Form.Group>

        {this.state.logEntry?.measurement &&
          <>
            <Form.Group controlId='formSensor'>
              <Form.Label>Sensor</Form.Label>
              {/* @TODO: Use sensor name here */}
              <Form.Control name='measurement' value={this.state.logEntry.measurement.sensor.id} readOnly />
            </Form.Group>
            <Form.Group controlId='formValue'>
              <Form.Label>Value</Form.Label>
              <Form.Control name='measurement' value={this.state.logEntry.measurement.value} readOnly />
            </Form.Group>
          </>}

        {this.state.logEntry?.loggedAt &&
          <Form.Group controlId='formLoggedAt'>
            <Form.Label>Logged at</Form.Label>
            <Form.Control name='loggedAt' value={this.state.logEntry.loggedAt} readOnly />
          </Form.Group>}
      </Form>
    )

    return (
      <>
        <Modal show={this.state.logEntry !== null} onHide={this.handleClose}>
          <Modal.Header closeButton>
            <Modal.Title>Add log entry</Modal.Title>
          </Modal.Header>
          <Modal.Body>
            {form}
          </Modal.Body>
          <Modal.Footer>
            <Button variant='secondary' onClick={this.handleClose} disabled={this.state.isSubmitting}>
              Cancel
            </Button>
            <Button variant='primary' onClick={this.handleSubmit} disabled={this.state.isSubmitting}>
              Save
            </Button>
          </Modal.Footer>
        </Modal>
      </>
    )
  }
}

LogEntry.propTypes = {
  postUrl: PropTypes.string.isRequired,
  messenger: PropTypes.instanceOf(Messenger).isRequired
}

export default LogEntry
