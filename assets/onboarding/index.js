/* Import this components styling */
import './onboarding.scss'

import { library, dom } from '@fortawesome/fontawesome-svg-core'
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons'

/* Require roboto typeface */
require('typeface-roboto')
const $ = require('jquery')
require('popper.js')
require('bootstrap')

library.add(faArrowLeft, faArrowRight)
dom.watch()

$(() => {
  $('.complete-onboarding').on('click', () => {
    document.cookie = 'onboarding-completed=1;path=/'
  })
})
