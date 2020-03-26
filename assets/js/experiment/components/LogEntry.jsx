/* global fetch */
import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Modal from 'react-bootstrap/Modal'
import Button from 'react-bootstrap/Button'
import Form from 'react-bootstrap/Form'
import Alert from 'react-bootstrap/Alert'

class LogEntry extends Component {
  constructor (props) {
    super(props)
    this.state = this.getInitialState()
  }

  getInitialState = () => ({
    show: false,
    isSubmitting: false,
    logEntry: {
      experiment: this.props.experiment['@id'],
      content: '',
      sensor: null, // '/api/sensors/fixture:device:001:humidity',
      loggedAt: (new Date()).toISOString()
    },
    violations: {},
    message: null,
    messageType: null
  })

  resetState = () => {
    this.setState(this.getInitialState())
  }

  handleShow = () => {
    this.setState({ show: true })
  }

  handleClose = () => {
    this.setState({ show: false })
  }

  handleChange = (event) => {
    const logEntry = this.state.logEntry
    this.setState({ logEntry: { ...logEntry, ...{ [event.target.name]: event.target.value } } })
  }

  handleSubmit = () => {
    this.setState({ isSubmitting: true }, () => {
      const data = this.state.logEntry
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
              this.setState({
                message: 'Log entry created',
                messageType: 'success'
              }, () => {
                setTimeout(() => {
                  this.resetState()
                  this.handleClose()
                }, 1000)
              })
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
          <Form.Control as='textarea' name='content' className={{ 'is-invalid': this.state.violations.content }} placeholder='Enter a log entry' value={this.state.logEntry.content} onChange={this.handleChange} />
          {this.state.violations.content && <div className='invalid-feedback'>{this.state.violations.content}</div>}
        </Form.Group>

        {this.state.logEntry.sensor &&
          <Form.Group controlId='formSensor'>
            <Form.Label>Sensor</Form.Label>
            <Form.Control name='sensor' value={this.state.logEntry.sensor} readOnly />
          </Form.Group>}

        {this.state.logEntry.loggedAt &&
          <Form.Group controlId='formLoggedAt'>
            <Form.Label>Logged at</Form.Label>
            <Form.Control name='loggedAt' value={this.state.logEntry.loggedAt} readOnly />
          </Form.Group>}
      </Form>
    )

    return (
      <>
        <Button variant='primary' onClick={this.handleShow}>Add log entry</Button>

        <Modal show={this.state.show} onHide={this.handleClose}>
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
  experiment: PropTypes.object.isRequired,
  postUrl: PropTypes.string.isRequired
}

export default LogEntry
