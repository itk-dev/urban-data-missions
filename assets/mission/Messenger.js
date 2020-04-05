/* global EventSource */
const EventEmitter = require('events')

class Messenger {
  constructor (options) {
    this.emitter = new EventEmitter()

    if (options.eventSourceUrl) {
      this.eventSource = new EventSource(options.eventSourceUrl)
      this.eventSource.onmessage = event => {
        const data = JSON.parse(event.data)
        this.emit('message', data)
      }
    }
  }

  emit () {
    this.emitter.emit.apply(this.emitter, arguments)
  }

  on () {
    this.emitter.on.apply(this.emitter, arguments)
  }
}

export default Messenger
