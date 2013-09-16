<?php
/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @package Yii2Debug
 */
class Yii2DebugProfilingPanel extends Yii2DebugPanel
{
    public function getTitle()
    {
        return 'Profiling';
    }

    public function getSummary()
    {
        $memory = sprintf('%.1f MB', $this->data['memory'] / 1048576);
        $time = number_format($this->data['time'] * 1000) . ' ms';

        return Yii::app()->controller->renderPartial('panels/_profilingSummary', array(
            'url' => $this->getUrl(),
            'memory' => $memory,
            'time' => $time,
        ));
    }

    public function getDetails()
    {
        $messages = $this->data['messages'];
        $timings = array();
        $stack = array();
        foreach ($messages as $i => $log) {
            list($token, , $category, $timestamp) = $log;
            $log[4] = $i;
            if (strpos($token, 'begin:') === 0) {
                $log[0] = $token = substr($token, 6);
                $stack[] = $log;
            } elseif (strpos($token, 'end:') === 0) {
                $log[0] = $token = substr($token, 4);
                if (($last = array_pop($stack)) !== null && $last[0] === $token) {
                    $timings[$last[4]] = array(count($stack), $token, $category, $timestamp - $last[3]);
                }
            }
        }
        $now = microtime(true);
        while (($last = array_pop($stack)) !== null) {
            $delta = $now - $last[3];
            $timings[$last[4]] = array(count($stack), $last[0], $last[2], $delta);
        }
        ksort($timings);

        $rows = array();
        foreach ($timings as $timing) {
            $row = array();
            $row['time'] = sprintf('%.1f ms', $timing[3] * 1000);
            $row['procedure'] = str_repeat('<span class="indent">→</span>', $timing[0]) . CHtml::encode($timing[1]);
            $row['category'] = CHtml::encode($timing[2]);
            $rows[] = $row;
        }

        $memory = sprintf('%.1f MB', $this->data['memory'] / 1048576);
        $time = number_format($this->data['time'] * 1000) . ' ms';

        return Yii::app()->controller->renderPartial('panels/_profilingDetails', array(
            'rows' => $rows,
            'memory' => $memory,
            'time' => $time,
        ), true);
    }

    public function getDataToSave()
    {
        $messages = Yii::getLogger()->getLogs(CLogger::LEVEL_PROFILE);

        return array(
            'memory' => memory_get_peak_usage(),
            'time' => microtime(true) - YII_BEGIN_TIME,
            'messages' => $messages,
        );
    }
}
