/* Import base styling */
import '../base.scss'

/* Import this components styling */
import './mission.scss'

/* Require roboto typeface */
require('typeface-roboto')
require('jquery')
require('popper.js')
require('bootstrap')

import '@fortawesome/fontawesome-free/js/all'

const L = require('leaflet')

const elMap = document.getElementById('map')
const mapOptions = JSON.parse(elMap.dataset.options)
const missions = mapOptions.missions || []

const map = L.map(elMap).setView([0, 0], 13)

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
}).addTo(map)

const markerIcon = L.divIcon({ className: 'map-marker-icon' })

const bounds = L.latLngBounds()
for (const mission of missions) {
  const marker = L.marker([mission.latitude, mission.longitude], {
    icon: markerIcon
  }).addTo(map)
  bounds.extend(marker.getLatLng())

  const showUrl = mapOptions.show_url_template.replace('%id%', mission.id)
  // @TODO: Design
  marker.bindPopup(`<div class="mission-title">${mission.title}</div> <a class="btn btn-secondary" href="${showUrl}">Show</a>`)
}

map.fitBounds(bounds)
