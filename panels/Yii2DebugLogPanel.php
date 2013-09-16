<?php
/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @package Yii2Debug
 */
class Yii2DebugLogPanel extends Yii2DebugPanel
{
    public function getTitle()
    {
        return 'Logs';
    }

    public function getSummary()
    {
        $errorsCount = 0;
        $warningsCount = 0;
        foreach ($this->data['messages'] as $log) {
            $level = $log[1];
            if ($level == CLogger::LEVEL_ERROR) {
                $errorsCount++;
            } elseif ($level == CLogger::LEVEL_WARNING) {
                $warningsCount++;
            }
        }

        $title = 'Logged ' . count($this->data['messages']) . ' messages';
        if ($errorsCount) {
            $title .= ', ' . $errorsCount . ' errors';
        }
        if ($warningsCount) {
            $title .= ', ' . $warningsCount . ' warnings';
        }

        return Yii::app()->controller->renderPartial('panels/_logSummary', array(
            'title' => $title,
            'url' => $this->getUrl(),
            'messagesCount' => array(
                'total' => count($this->data['messages']),
                'errors' => $errorsCount,
                'warnings' => $warningsCount,
            ),
        ));
    }

    public function getDetails()
    {
        $rows = array();
        foreach ($this->data['messages'] as $log) {
            $row = array();
            list ($message, $level, $category, $time) = $log;
            $row['time'] = date('H:i:s.', $time) . sprintf('%03d', (int)(($time - (int)$time) * 1000));
            $row['category'] = $category;
            $row['level'] = $level;

            $traces = array();
            if (($lines = explode(PHP_EOL . 'Stack trace:' . PHP_EOL, $message, 2)) !== false) {
                $message = $lines[0];
                if (isset($lines[1])) {
                    $traces = array_merge(
                        array('Stack trace:'),
                        explode(PHP_EOL, $lines[1])
                    );
                } elseif (($lines = explode(PHP_EOL . 'in ', $message)) !== false) {
                    $message = array_shift($lines);
                    $traces = $lines;
                }
            }
            $row['message'] = nl2br(CHtml::encode($message));
            $row['traces'] = $traces;

            $row['class'] = '';
            if ($level == CLogger::LEVEL_ERROR) {
                $row['class'] = 'danger';
            } elseif ($level == CLogger::LEVEL_WARNING) {
                $row['class'] = 'warning';
            } elseif ($level == CLogger::LEVEL_INFO) {
                $row['class'] = 'info';
            }

            $rows[] = $row;
        }

        return Yii::app()->controller->renderPartial('panels/_logDetails', array(
            'rows' => $rows,
        ));
    }

    public function getDataToSave()
    {
        $messages = Yii::getLogger()->getLogs(implode(',', array(
            CLogger::LEVEL_ERROR,
            CLogger::LEVEL_INFO,
            CLogger::LEVEL_WARNING,
            CLogger::LEVEL_TRACE,
        )));

        return array(
            'messages' => $messages,
        );
    }
}
