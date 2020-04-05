const EventEmitter = require('events')

class Messenger {
  constructor () {
    this.emitter = new EventEmitter()
  }

  emit () {
    this.emitter.emit.apply(this.emitter, arguments)
  }

  on () {
    this.emitter.on.apply(this.emitter, arguments)
  }
}

export default Messenger
