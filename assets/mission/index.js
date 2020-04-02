/* Import base styling */
import '../base.scss'

/* Import this components styling */
import './mission.scss'

import '@fortawesome/fontawesome-free/js/all'
require('jquery')
require('popper.js')
require('bootstrap')

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
  marker.bindPopup(`<p class="mb-0 h4">${mission.title}</p><p class="text-primary"><i class="fas fa-map-marker-alt mr-1"></i>${mission.location}</p><p>${mission.desctiption}</p><a href="${showUrl}" class="btn btn-primary btn-sm btn-block">Show mission</a>`)
}

map.fitBounds(bounds)

