import './mission_sensor.scss'

import 'select2'

const $ = require('jquery')

$(() => {
  const formatSensor = (sensor) => {
    if (!sensor.id) {
      return sensor.text
    }

    const result = $('<div class="sensor-search-result"/>')
    result.append($('<div class="sensor-id"/>').html('id: ' + sensor.id))
    if (sensor.type) {
      result.append($('<div class="sensor-type"/>').html('type: ' + sensor.type))
    }

    return result
  }

  const $search = $('#mission_sensor_search')
  const options = {
    ajax: {
      ...$search.data('ajax-options'),
      ...{
        processResults: (data) => {
          // console.log('processResults', data)
          // Transforms the top-level key of the response object from 'items' to 'results'
          return {
            results: data.data
          }
        }
      }
    },
    templateResult: formatSensor,
    templateSelection: formatSensor
  }
  console.log(options)
  $search.select2(options)
})
