/* global AbortController, fetch */
import './mission_sensor.scss'

import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import debounce from 'debounce'

import '@fortawesome/fontawesome-free/js/all'
import Form from 'react-bootstrap/Form'
import Alert from 'react-bootstrap/Alert'
import ListGroup from 'react-bootstrap/ListGroup'
import Translator from '../translations'

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
      return query && <Alert variant='warning' className='py-1'>No sensors matching <code className='sensor-query'>{query}</code> found.</Alert>
    }

    return (
      <>
        <Alert variant='success' className='py-1'>
          {query ? (
            <span>
              {Translator.transChoice(
                '{0}No results match %query%|{1}One result matching %query%|]1,Inf]%count% results matching %query%',
                data.length,
                { query: query }
              )}
            </span>
          ) : (
            <span>
              {Translator.transChoice(
                '{0}No results|{1}One result|]1,Inf]%count% results',
                data.length
              )}
            </span>
          )}
        </Alert>

        <ListGroup variant='flush'>
          {data.map((item) => (
            <ListGroup.Item
              key={item.id}
              className='sensor-search-result pb-3'
            >
              <div className='d-flex w-100 justify-content-between'>
                <h2 className='h4 mb-1'>{item.id}</h2>
                <small />
              </div>
              <p className='mb-1'>
                {item.type}
              </p>

              {item._metadata.mission_sensor ? (
                <p className='text-primary'>

                  {Translator.trans(
                    'Already included in mission'
                  )}{' '}
                  <a
                    className='btn btn-primary btn-sm ml-3'
                    href={getEditSensorUrl(item)}
                  >
                    {Translator.trans('Edit')}
                  </a>
                </p>

              ) : (
                <a
                  className='btn btn-success btn-sm'
                  href={getAddSensorUrl(item)}
                >
                  <i className='fas fa-plus-circle mr-1' />{Translator.trans('Add')}
                </a>
              )}
            </ListGroup.Item>
          ))}
        </ListGroup>
      </>
    )
  }

  return (
    <div className='mission-sensor-search mt-3'>
      <Form.Group controlId='formContent'>
        <Form.Label className='sr-only'>{Translator.trans('Search')}</Form.Label>
        <Form.Control placeholder={Translator.trans('Search for a sensor')} value={query} onChange={(event) => setQuery(event.target.value)} size='lg' />
      </Form.Group>

      {error && <Alert variant='danger'>{Translator.trans('Error: %error%', { error: error })}</Alert>}

      {isLoading
        ? <Alert variant='info' className='py-1'>{query ? <span>{Translator.trans('Searching for %query% …', { query: query })}</span> : <span>{Translator.trans('Searching …')}</span>}</Alert>
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
