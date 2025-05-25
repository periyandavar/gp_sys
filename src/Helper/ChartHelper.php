<?php

/**
 * Helper File
 */

namespace System\Helper;

/**
 * ChartHelper to generate chart
 */
class ChartHelper
{
    /**
     * Helper function to create charts
     *
     * @param string   $id        Chart id
     * @param int|null $height    Chart Height
     * @param int|null $width     Chart width
     * @param string   $className Chart class
     *
     * @return string
     */
    public static function createChart(string $id, ?int $height = null, ?int $width = null, string $className = ''): string
    {
        $chart = "<canvas id='$id' class='$className' ";
        $chart .= $height != null ? " height='$height' " : '';
        $chart .= $width != null ? " width='$width' " : '';
        $chart .= '> Your browser does not support HTML5 Canvas </canvas>';

        return $chart;
    }
    /**
     * Generates charts
     *
     * @param string $id         Id
     * @param array  $data       Data
     * @param array  $properties Properties arry
     *
     * @return string
     */
    public static function generateChart(string $id, array $data, array $properties = [])
    {
        $dataValue = '';
        $xAxis = $properties['xAxis'] ?? '';
        $yAxis = $properties['yAxis'] ?? '';
        $maxVal = $properties['maxVal'] ?? 0;
        $color = $properties['color'] ?? '#000000';
        $yDots = $properties['yDots'] ?? '10';
        foreach ($data as $temp) {
            $dataValue .= "['$temp[0]', $temp[1]],";
        }

        return <<<EOF
        <script>
            obj = {
                id: "$id",
                data: [$dataValue],
                xAxis: "$xAxis",
                yAxis: "$yAxis",
                maxVal: "$maxVal",
                color: "$color",
                yDots: "$yDots"
            }
            barChart(obj)
            window.addEventListener('resize', () => {
                
                obj = {
                    id: "$id",
                    data: [$dataValue],
                    xAxis: "$xAxis",
                    yAxis: "$yAxis",
                    maxVal: "$maxVal",
                    color: "$color",
                    yDots: "$yDots"
                }
                barChart(obj)
            });
        </script>
        EOF;
    }
    /**
     * Helper function will returns js used in the Charts
     *
     * @return string
     */
    public static function getChartJs()
    {
        $script = <<<EOF
       <script>
       var ctr = 0;
       function barChart(obj) {
       let numMarkers, axisName;
       let canvas = document.getElementById(obj.id);
       let context = canvas.getContext('2d');
       let data = obj.data;
       let cMargin = 10;
       let cSpace = 40;
       let bMargin = 8;
       let color = obj.color;
       let cHeight = canvas.height - (2 * cMargin) - (2 * cSpace);
       let cWidth = canvas.width - (2 * cMargin) - (2 * cSpace);
       let cMarginSpace = cMargin + cSpace;
       let cMarginHeight = cMargin + cHeight;
       let maxVal = obj.maxVal == null ? 0 : obj.maxVal;
       let yDots = obj.yDots == null ? 10 : obj.yDots;
       context.lineWidth = "2.0";
       context.font = "13px Arial";
       drawAxis(context, cMarginSpace, cMarginHeight, cMarginSpace, cMargin);
       drawAxis(context, cMarginSpace, cMarginHeight, cMarginSpace + cWidth, cMarginHeight);
       context.lineWidth = "1.0";
       for (let i = 0; i < data.length; i++) {
           let value = parseInt(data[i][1]);
           if (value > maxVal) {
               maxVal = value;
           }
       }
       numMarkers = parseInt(maxVal / yDots);
       context.textAlign = "right";
       context.fillStyle = "#000";
       for (let i = 0; i <= yDots; i++) {
           let markerVal = i * numMarkers;
           let yMarkers = cMarginHeight - ((i * numMarkers * cHeight) / maxVal);
           context.fillText(markerVal, cMarginSpace - 5, yMarkers, cSpace);
       }
       context.textAlign = 'center';
       bWidth = (cWidth / data.length) - (2 * bMargin);
       for (var i = 0; i < data.length; i++) {
           let value = data[i][0];
           markerXPos = cMarginSpace + bMargin + (i * (bWidth + bMargin)) + (bWidth / 2);
           markerYPos = cMarginHeight + 14;
           context.fillText(value, markerXPos, markerYPos, bWidth);
       }

       context.save();

       context.translate(cMargin + 10, cHeight / 2);
       context.rotate(-Math.PI / 2);
       axisName = obj.yAxis == null ? "" : obj.yAxis;
       context.fillText(axisName, 0, 0);

       context.restore();
       axisName = obj.xAxis == null ? "" : obj.xAxis;

       context.fillText(axisName, cMarginSpace + (cWidth / 2), cMarginHeight + 40);

       function draw() {
           for (let i = 0; i < data.length; i++) {
               let bHt = (parseInt(data[i][1]) * cHeight / maxVal) / 100 * ctr;
               let bX = cMarginSpace + (i * (bWidth + bMargin)) + bMargin;
               let bY = cMarginHeight - bHt - 2;
               drawRectangle(context, bX, bY, bWidth, bHt, color);
           }
           if (ctr < 100) {
               ctr = ctr + 1;
               setTimeout(draw, 20);
           }
       }
       draw();
       }
       function drawRectangle(context, x, y, w, h, color = "#00FF00") {
           context.beginPath();
           context.rect(x, y, w, h);
           context.closePath();
           context.stroke();
           context.fillStyle = color;
           context.fill();
       }

       function drawAxis(context, x, y, X, Y) {
           context.beginPath();
           context.moveTo(x, y);
           context.lineTo(X, Y);
           context.closePath();
           context.stroke();
       }
       </script>
       EOF;

        return $script;
    }
}
