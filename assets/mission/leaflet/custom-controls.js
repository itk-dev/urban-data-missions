// @see https://leafletjs.com/examples/extending/extending-3-controls.html#controls

const L = require('leaflet')

L.Control.MarkerTextFilter = L.Control.extend({
  onChange: function (event) {
    const bounds = L.latLngBounds()
    const text = event.target.value
    for (const group of Object.values(this._layerGroups)) {
      for (const marker of group.getLayers()) {
        const isMatch = !text || this.options.matcher(text, marker)
        marker.setIcon(isMatch ? this.options.icon : this.options.hiddenIcon)
        if (isMatch) {
          bounds.extend(marker.getLatLng())
        }
      }
    }
    if (this.options.zoomToMatches && bounds.isValid()) {
      this._map.fitBounds(bounds)
    }
  },

  onAdd: function (map) {
    this._map = map
    this._input = L.DomUtil.create('input')
    if (this.options.placeholder) {
      this._input.placeholder = this.options.placeholder
    }
    if (this.options.classNames) {
      this._input.classList.add(...this.options.classNames)
    }
    this._onChange = this.onChange.bind(this)
    L.DomEvent.on(this._input, 'keyup', this._onChange)

    this._layerGroups = {}

    const layerGroups = this.options.layerGroups
    for (const group in layerGroups) {
      this._layerGroups[group] = layerGroups[group]
    }

    return this._input
  },

  onRemove: function (map) {
    this._input.removeEventListener('keyup', this._onChange)
  }
})

L.control.markerTextFilter = function (options) {
  if (typeof options.matcher !== 'function') {
    throw new Error('markerTextFilter: option "matcher" is not a function')
  }
  if (!(options.icon instanceof L.Icon)) {
    throw new Error('markerTextFilter: option "icon" is not an icon')
  }
  if (!(options.hiddenIcon instanceof L.Icon)) {
    throw new Error('markerTextFilter: option "hiddenIcon" is not an icon')
  }

  return new L.Control.MarkerTextFilter(options)
}
