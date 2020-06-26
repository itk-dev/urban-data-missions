/* Import base styling */
import '../base.scss'

/* Import this components styling */
import './mission.scss'

import 'bootstrap/js/dist/collapse'

/* Import FontAwesome icons */
import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faMapMarkerAlt } from '@fortawesome/free-solid-svg-icons/faMapMarkerAlt'
import { faBars } from '@fortawesome/free-solid-svg-icons/faBars'
import { faPlus } from '@fortawesome/free-solid-svg-icons/faPlus'
import { faTimesCircle } from '@fortawesome/free-solid-svg-icons/faTimesCircle'
import { faCircle } from '@fortawesome/free-solid-svg-icons/faCircle'
import Translator from '../translations'

library.add(faMapMarkerAlt, faBars, faPlus, faTimesCircle, faCircle)
dom.watch()

const L = require('leaflet')
require('./leaflet/custom-controls')

const elMap = document.getElementById('map')
const mapOptions = JSON.parse(elMap.dataset.options)
const missions = mapOptions.missions || []

const map = L.map(elMap).setView([0, 0], 13)

map.removeControl(map.zoomControl)

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>'
}).addTo(map)

const markerIcon = L.divIcon({
  className: 'map-marker-icon',
  html: '<i class="fas fa-circle fa-lg"></i>'
})

const markerHiddenIcon = L.divIcon({
  className: 'map-marker-icon map-marker-icon-hidden',
  html: '<i class="fas fa-circle fa-lg"></i>'
})

const buildSearch = (mission) => {
  return [
    mission.title,
    mission.description
  ].join(' ').toLowerCase()
}

// Missions grouped by theme
const themeMissions = {}
const bounds = L.latLngBounds()
for (const mission of missions) {
  const marker = L.marker([mission.latitude, mission.longitude], {
    icon: markerIcon,
    data: mission,
    search: buildSearch(mission)
  })
  bounds.extend(marker.getLatLng())

  const showUrl = mapOptions.show_url_template.replace('%id%', mission.id)
  marker.bindPopup(`<p class="mb-0 h4">${mission.title}</p><a href="/theme/${mission.theme.id}" class="text-primary">${mission.theme.title}</a><p class="text-primary"><i class="fas fa-map-marker-alt mr-1"></i>${mission.location}</p><p>${mission.description}</p><a href="${showUrl}" class="btn btn-primary btn-sm btn-block">${Translator.trans('Show mission')}</a>`)

  let layerGroup = themeMissions[mission.theme.title]
  if (!layerGroup) {
    layerGroup = L.layerGroup()
    layerGroup.addTo(map)
    themeMissions[mission.theme.title] = layerGroup
  }
  marker.addTo(layerGroup)
}

map.fitBounds(bounds)

L.control.layers(null, themeMissions, {
  position: 'topleft',
  collapsed: true

}).addTo(map)

L.control.markerTextFilter({
  position: 'topright',
  layerGroups: themeMissions,
  icon: markerIcon,
  hiddenIcon: markerHiddenIcon,
  placeholder: Translator.trans('Search for a mission'),
  classNames: ['mission-search'],
  zoomToMatches: true,
  matcher: (text, marker) => {
    return marker.options.search.indexOf(text.toLowerCase()) > -1
  }
}).addTo(map)

// @TODO: Translate theme missions label.
$(".leaflet-control-layers-overlays").prepend("<label><strong>Mission themes</strong></label>");

// @TODO: Translate link text.
$(".leaflet-control-layers-overlays").append("<a href='/cms' class='mt-3 d-block text-primary'>Show guide</a>");
