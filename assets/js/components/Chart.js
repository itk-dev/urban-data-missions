// https://www.amcharts.com/docs/v4/concepts/data/#Parsing_dates_in_data
// https://www.amcharts.com/docs/v4/tutorials/formatting-date-time-and-numbers-using-intl-object/#Date_format_options

import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
import am4themes_animated from '@amcharts/amcharts4/themes/animated' // eslint-disable-line camelcase

am4core.useTheme(am4themes_animated)

class Chart {
  constructor (options = {}) {
    this.options = options
    this.chart = am4core.create(options.el || 'chart', am4charts.XYChart)
    this.buildAxes(options.axes || {})
    this.series = []

    if (!options.series) {
      throw new Error('options.series not set in Chart constructor options')
    }

    for (const [field, seriesOptions] of Object.entries(options.series)) {
      this.series.push(this.addSeries(field, seriesOptions))
    }

    if (options.legend) {
      this.buildLegend(options.legend)
    }

    if (options.cursor) {
      this.buildCursor(options.cursor)
    }

    if (options.scrollbars) {
      this.buildScrollbars(options.scrollbars)
    }
  }

  buildAxes (options) {
    // Create axes
    this.dateAxis = this.chart.xAxes.push(new am4charts.DateAxis())

    // https://www.amcharts.com/docs/v4/concepts/axes/date-axis/#List_of_available_time_units
    this.dateAxis.dateFormats.setKey('hour', 'HH:mm:ss')
    // Show a full date when the hour changes
    this.dateAxis.periodChangeDateFormats.setKey('hour', 'HH:mm:ss\nYYYY-MM-dd')

    this.valueAxis = this.chart.yAxes.push(new am4charts.ValueAxis()) // eslint-disable-line

    // // dateAxis.start = 0.79;
    // dateAxis.keepSelection = true
  }

  buildCursor (options) {
    // Make a panning cursor
    const cursor = new am4charts.XYCursor()
    cursor.behavior = 'panXY'
    cursor.xAxis = this.dateAxis
    cursor.snapToSeries = this.series

    this.chart.cursor = cursor
  }

  buildLegend (options) {
    this.chart.legend = new am4charts.Legend()
  }

  buildScrollbars (options) {
    // Create vertical scrollbar and place it before the value axis
    this.chart.scrollbarY = new am4core.Scrollbar()
    this.chart.scrollbarY.parent = this.chart.leftAxesContainer
    this.chart.scrollbarY.toBack()

    // Create a horizontal scrollbar with previe and place it underneath the date axis
    this.chart.scrollbarX = new am4charts.XYChartScrollbar()
    // this.chart.scrollbarX.series.push(this.series[0]);
    this.chart.scrollbarX.parent = this.chart.bottomAxesContainer
  }

  addSeries (field, options = {}) {
    // Create series
    const series = this.chart.series.push(new am4charts.LineSeries())
    series.dataFields.dateX = 'date'
    series.dataFields.valueY = field
    series.name = options.name || field
    series.tooltipText = '{dateX}: [b]{valueY}[/]'
    series.strokeWidth = 2
    series.minBulletDistance = 15

    // Drop-shaped tooltips
    series.tooltip.background.cornerRadius = 20
    series.tooltip.background.strokeOpacity = 0
    series.tooltip.pointerOrientation = 'vertical'
    series.tooltip.label.minWidth = 40
    series.tooltip.label.minHeight = 40
    series.tooltip.label.textAlign = 'middle'
    series.tooltip.label.textValign = 'middle'

    // Make bullets grow on hover
    const bullet = series.bullets.push(new am4charts.CircleBullet())
    bullet.circle.strokeWidth = 2
    bullet.circle.radius = 4
    bullet.circle.fill = am4core.color('#fff')

    const bullethover = bullet.states.create('hover')
    bullethover.properties.scale = 1.3

    return series
  }

  addData () {
    this.chart.addData.apply(this.chart, arguments)
  }

  getData () {
    return this.chart.data
  }
}

export default Chart
