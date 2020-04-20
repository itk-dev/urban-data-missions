/* global AbortController, fetch */
import './mission_sensor.scss'

import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import debounce from 'debounce'

import '@fortawesome/fontawesome-free/js/all'
import Form from 'react-bootstrap/Form'
import Alert from 'react-bootstrap/Alert'

// @see https://www.robinwieruch.de/react-hooks-fetch-data
function App (props) {
  const [data, setData] = useState([])
  const [query, setQuery] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState(null)

  const getAddSensorUrl = (sensor) => {
    return props.addSensorUrl.replace('%sensor%', encodeURIComponent(sensor.id))
  }

  const getEditSensorUrl = (sensor) => {
    return props.editSensorUrl.replace('%sensor%', encodeURIComponent(sensor._metadata.mission_sensor.id))
  }

  const abortController = new AbortController()

  const doSearch = debounce(
    (query) => {
      // abortController = new AbortController()
      setIsLoading(true)
      const url = props.searchUrl + '?q=' + encodeURIComponent(query)
      fetch(url, {
        headers: {
          accept: 'application/ld+json'
        },
        signal: abortController.signal
      })
        .then((response) => {
          ;; console.log('response', response)
          if (!response.ok) {
            throw new Error(response.statusText)
          }
          return response.json()
        })
        .then(data => {
          setData(data.data)
        })
        .catch(error => {
          if (error.name === 'AbortError') {
            return
          }
          setError(error.message)
        })
        .finally(() => setIsLoading(false))
    },
    200
  )

  useEffect(() => {
    doSearch(query)

    return () => {
      abortController.abort()
    }
  }, [query])

  const renderData = () => {
    if (query && data.length === 0) {
      return query && <Alert variant='warning'>No sensors matching <code className='sensor-query'>{query}</code> found.</Alert>
    }

    // @TODO: Design
    return (
      <>
        <Alert variant='success'>{query ? <span>Results matching <code className='sensor-query'>{query}</code> ({data.length})</span> : <span>Results ({data.length})</span>}</Alert>
        <ol className='list-unstyled'>
          {data.map(item => (
            <li key={item.id} className='sensor-search-result'>
              <div className='sensor-id'>id: {item.id}</div>
              <div className='sensor-type'>type: {item.type}</div>
              {item._metadata.mission_sensor
                ? <Alert variant='info'>Already included in mission <a className='btn btn-primary btn-sm' href={getEditSensorUrl(item)}>Edit</a></Alert>
                : <a className='btn btn-success' href={getAddSensorUrl(item)}>Add</a>}
            </li>
          ))}
        </ol>
      </>
    )
  }

  return (
    <div className='mission-sensor-search'>
      <Form.Group controlId='formContent'>
        <Form.Label className='sr-only'>Search</Form.Label>
        <Form.Control placeholder='Search for a sensor' value={query} onChange={(event) => setQuery(event.target.value)} />
      </Form.Group>

      {error && <Alert variant='danger'>Error: {error}</Alert>}

      {isLoading
        ? <Alert variant='info'>{query ? <span>Searching for <code className='sensor-query'>{query}</code> …</span> : <span>Searching …</span>}</Alert>
        : renderData()}
    </div>
  )
}

App.propTypes = {
  searchUrl: PropTypes.string.isRequired,
  addSensorUrl: PropTypes.string.isRequired,
  editSensorUrl: PropTypes.string.isRequired
}

const el = document.getElementById('app')
const options = JSON.parse(el.dataset.options || '{}')

ReactDOM.render(
  <App {...options} />,
  document.getElementById('app')
)
