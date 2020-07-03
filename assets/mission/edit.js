/* Import this components styling */
import './mission.scss'

import 'select2'

/* Import FontAwesome icons */
import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faCircle } from '@fortawesome/free-solid-svg-icons/faCircle'
import { faLocationArrow } from '@fortawesome/free-solid-svg-icons/faLocationArrow'

library.add(faCircle, faLocationArrow)
dom.watch()

const $ = require('jquery')

// @see https://symfony.com/doc/current/form/dynamic_form_modification.html#dynamic-generation-for-submitted-forms
$(() => {
  const buildCollectionTypes = (context) => {
    // Collection types
    // @see https://symfony.com/doc/current/reference/forms/types/collection.html#adding-and-removing-items
    $('[data-collection-add-new-widget-selector]', context).on('click', function () {
      const $container = $($(this).data('collection-add-new-widget-selector'))
      let counter = $container.data('widget-counter') || $container.children().length
      const template = $container.attr('data-prototype').replace(/__name__/g, counter)
      counter++
      $container.data('widget-counter', counter)
      const item = $(template)
      $container.append(item)
      buildCollectionTypes(item)
      // buildOptionsForms(item)
    })

    $('[data-collection-remove-widget-selector]', context).on('click', function () {
      const $container = $($(this).data('collection-remove-widget-selector'))
      $container.remove()
    })
  }

  buildCollectionTypes()

  const L = require('leaflet')
  require('leaflet.locatecontrol')

  const latitude = document.getElementById('mission_latitude')
  const longitude = document.getElementById('mission_longitude')
  if (latitude && longitude) {
    // Insert map into form.
    const el = document.createElement('div')
    el.id = 'map'
    el.classList.add('mission-location-map')
    latitude.parentNode.parentNode.insertBefore(el, latitude.parentNode)

    latitude.parentNode.classList.add('map-shown')
    longitude.parentNode.classList.add('map-shown')

    let [lat, lng] = [parseFloat(latitude.value), parseFloat(longitude.value)]
    if (isNaN(lat) || isNaN(lng)) {
      [lat, lng] = [56.1525791, 10.2008397] // Aarhus City Hall
    }

    const map = L.map(el, {
      // @TODO Prevent zooming when scrolling past map.
      scrollWheelZoom: false
    }).setView([lat, lng], 13)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
    }).addTo(map)

    const markerIcon = L.divIcon({
      className: 'map-marker-icon',
      // iconSize: L.point(24, 24),
      html: '<i class="fas fa-circle fa-lg"></i>'
    })

    const center = L.marker(map.getCenter(), {
      icon: markerIcon
    }).addTo(map)

    map.on('move', (event) => {
      center.setLatLng(map.getCenter())
    })

    const updateLocation = () => {
      const center = map.getCenter()
      latitude.value = center.lat
      longitude.value = center.lng
    }

    map.on('moveend', (event) => {
      updateLocation()
    })

    updateLocation()

    map.on('locationerror', (event) => console.log(event.message))
    L.control.locate({
      // @TODO Set title (https://github.com/domoritz/leaflet-locatecontrol/blob/gh-pages/src/L.Control.Locate.js#L300)
    }).addTo(map)

    // TODO: Change get location button icon to fas fa-location-arrow
  }
})
