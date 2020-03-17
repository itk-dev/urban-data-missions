import '../css/app.scss'

if (typeof window.APP_CONFIG !== 'undefined') {
  if (window.APP_CONFIG.eventSourceUrl) {
    const eventSource = new EventSource(APP_CONFIG.eventSourceUrl)
    eventSource.onmessage = event => {
      // Will be called every time an update is published by the server
      console.log(JSON.parse(event.data))
    }
  }
}
