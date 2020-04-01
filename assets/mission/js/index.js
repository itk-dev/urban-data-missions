import '../css/index.scss'

const L = require('leaflet')

const el = document.getElementById('map')
const options = JSON.parse(el.dataset.options)
const missions = options.missions || []

const map = L.map(el).setView([0, 0], 13)

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

  const showUrl = options.show_url_template.replace('%id%', mission.id)
  // @TODO: Design
  marker.bindPopup(`<div class="mission-title">${mission.title}</div> <a class="btn btn-secondary" href="${showUrl}">Show</a>`)
}

map.fitBounds(bounds)
