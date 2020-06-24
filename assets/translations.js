const Translator = require('bazinga-translator')

const config = require('./translations/config')
for (const [key, value] of Object.entries(config)) {
  Translator[key] = value
}
const da = require('./translations/messages/da.json')
Translator.fromJSON(da)

export default Translator
