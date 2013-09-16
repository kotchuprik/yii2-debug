<?php
/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @package Yii2Debug
 */
class Yii2DebugDbPanel extends Yii2DebugPanel
{
    /**
     * @var bool вставлять или нет значения параметров в sql-запрос
     */
    public $insertParamValues = true;

    private $_timings;

    private $_resume;

    /**
     * @var CTextHighlighter
     */
    private $_hl;

    public function getTitle()
    {
        return 'Database';
    }

    public function getSummary()
    {
        $timings = $this->_calculateTimings();
        $queryCount = count($timings);
        if (count($queryCount) == 0) {
            return '';
        }

        $queryTime = 0;
        foreach ($timings as $timing) {
            $queryTime += $timing[4];
        }
        $queryTime = number_format($queryTime * 1000) . ' ms';

        return Yii::app()->controller->renderPartial('panels/_dbSummary', array(
            'queryCount' => $queryCount,
            'queryTime' => $queryTime,
            'url' => $this->getUrl(),
        ));
    }

    public function getDetails()
    {
        $queriesCount = count($this->_calculateTimings());
        $resumeCount = count($this->_calculateResume());
        $connectionsCount = count($this->data['connections']);

        return $this->_renderTabs(array(
            array(
                'label' => 'Queries (' . $queriesCount . ')',
                'content' => $this->_getQueriesDetails(),
                'active' => true,
            ),
            array(
                'label' => 'Resume (' . $resumeCount . ')',
                'content' => $this->_getResumeDetail(),
            ),
            array(
                'label' => 'Connections (' . $connectionsCount . ')',
                'content' => $this->_getConnectionsDetail(),
            ),
        ));
    }

    public function getDataToSave()
    {
        $messages = Yii::getLogger()->getLogs(CLogger::LEVEL_PROFILE, 'system.db.CDbCommand.*');

        $connections = array();
        foreach (Yii::app()->getComponents() as $id => $component) {
            if ($component instanceof CDbConnection) {
                /* @var CDbConnection $component */
                $connections[$id] = array(
                    'class' => get_class($component),
                    'driver' => $component->getDriverName(),
                    'server' => $component->getServerVersion(),
                    'info' => $component->getServerInfo(),
                );
            }
        }

        return array(
            'messages' => $messages,
            'connections' => $connections,
        );
    }

    /**
     * @return string html-контент закладки со списком sql-запросов
     */
    protected function _getQueriesDetails()
    {
        $rows = array();
        foreach ($this->_calculateTimings() as $timing) {
            $row = array();
            $time = $timing[3];
            $row['time'] = date('H:i:s.', $time) . sprintf('%03d', (int)(($time - (int)$time) * 1000));
            $row['duration'] = sprintf('%.1f ms', $timing[4] * 1000);
            $procedure = $this->_formatSql($timing[1]);
            if ($this->highlightCode) {
                $row['procedure'] = $this->_highlightSql($procedure);
            } else {
                $row['procedure'] = CHtml::encode($procedure);
            }

            $rows[] = $row;
        }

        return Yii::app()->controller->renderPartial('panels/_dbQueriesDetails', array(
            'rows' => $rows,
        ), true);
    }

    /**
     * @return string html-контент закладки с группировкой sql-запросов
     */
    protected function _getResumeDetail()
    {
        $rows = array();
        foreach ($this->_calculateResume() as $item) {
            $row = array();
            list($query, $count, $total, $min, $max) = $item;
            if ($this->highlightCode) {
                $row['query'] = $this->_highlightSql($query);
            } else {
                $row['query'] = CHtml::encode($query);
            }
            $row['count'] = $count;
            $row['avg'] = sprintf('%.1f ms', $total * 1000 / $count);
            $row['total'] = sprintf('%.1f ms', $total * 1000);
            $row['min'] = sprintf('%.1f ms', $min * 1000);
            $row['max'] = sprintf('%.1f ms', $max * 1000);

            $rows[] = $row;
        }

        return Yii::app()->controller->renderPartial('panels/_dbResumeDetails', array(
            'rows' => $rows,
        ), true);
    }

    /**
     * @return string html-контент закладки с детальной информацией активных
     * подключений к базам данных
     */
    protected function _getConnectionsDetail()
    {
        $content = '';
        foreach ($this->data['connections'] as $id => $connection) {
            $caption = 'Component: ' . $id . '(' . $connection['class'] . ')';
            unset($connection['class']);
            foreach (explode('  ', $connection['info']) as $line) {
                list($key, $value) = explode(': ', $line, 2);
                $connection[$key] = $value;
            }
            unset($connection['info']);
            $content .= $this->_renderDetails($caption, $connection);
        }

        return $content;
    }

    /**
     * Группировка времени выполнения sql-запросов
     *
     * @return array
     */
    protected function _calculateTimings()
    {
        if ($this->_timings !== null) {
            return $this->_timings;
        }

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
                    $timings[$last[4]] = array(count($stack), $token, $category, $last[3], $timestamp - $last[3]);
                }
            }
        }

        $now = microtime(true);
        while (($last = array_pop($stack)) !== null) {
            $delta = $now - $last[3];
            $timings[$last[4]] = array(count($stack), $last[0], $last[2], $last[3], $delta);
        }
        ksort($timings);

        return $this->_timings = $timings;
    }

    /**
     * Группировка sql-запросов
     *
     * @return array
     */
    protected function _calculateResume()
    {
        if ($this->_resume !== null) {
            return $this->_resume;
        }
        $resume = array();
        foreach ($this->_calculateTimings() as $timing) {
            $duration = $timing[4];
            $query = $this->_formatSql($timing[1]);
            $key = md5($query);
            if (!isset($resume[$key])) {
                $resume[$key] = array($query, 1, $duration, $duration, $duration);
            } else {
                $resume[$key][1]++;
                $resume[$key][2] += $duration;
                if ($resume[$key][3] > $duration) {
                    $resume[$key][3] = $duration;
                }
                if ($resume[$key][4] < $duration) {
                    $resume[$key][4] = $duration;
                }
            }
        }
        usort($resume, array($this, '_compareResume'));

        return $this->_resume = $resume;
    }

    protected function _compareResume($a, $b)
    {
        if ($a[2] == $b[2]) {
            return 0;
        }

        return $a[2] < $b[2] ? 1 : -1;
    }

    /**
     * Выделение sql-запроса из лога и подстановка параметров
     *
     * @param string $message
     *
     * @return string
     */
    protected function _formatSql($message)
    {
        $sqlStart = strpos($message, '(') + 1;
        $sqlEnd = strrpos($message, ')');
        $sql = substr($message, $sqlStart, $sqlEnd - $sqlStart);
        if (strpos($sql, '. Bound with ') !== false) {
            list($query, $params) = explode('. Bound with ', $sql);
            if (!$this->insertParamValues) {
                return $query;
            }
            $sql = strtr($query, $this->_parseParamsSql($params));
        }

        return $sql;
    }

    /**
     * Парсинг строки с параметрами
     * @param string $params
     *
     * @return array key/value
     */
    protected function _parseParamsSql($params)
    {
        $binds = array();
        $pos = 0;
        while (preg_match('/(\:[a-z0-9\.\_\-]+)\s*\=\s*/', $params, $m, PREG_OFFSET_CAPTURE, $pos)) {
            $start = $m[0][1] + strlen($m[0][0]);
            $key = $m[1][0];
            if (($params{$start} == '"') || ($params{$start} == "'")) {
                $quote = $params{$start};
                $pos = $start;
                while (($pos = strpos($params, $quote, $pos + 1)) !== false) {
                    $slashes = 0;
                    while ($params{$pos - $slashes - 1} == '\\') {
                        $slashes++;
                    }
                    if ($slashes % 2 == 0) {
                        $binds[$key] = substr($params, $start, $pos - $start + 1);
                        $pos++;
                        break;
                    }
                }
            } elseif (($end = strpos($params, ',', $start + 1)) !== false) {
                $binds[$key] = substr($params, $start, $end - $start);
                $pos = $end + 1;
            } else {
                $binds[$key] = substr($params, $start, strlen($params) - $start);
                break;
            }
        }

        return $binds;
    }

    /**
     * Подсветка sql-кода
     *
     * @param string $sql
     *
     * @return string
     */
    protected function _highlightSql($sql)
    {
        if ($this->_hl === null) {
            $this->_hl = Yii::createComponent(array(
                'class' => 'CTextHighlighter',
                'language' => 'sql',
                'showLineNumbers' => false,
            ));
        }
        $html = $this->_hl->highlight($sql);

        return strip_tags($html, '<span>');
    }
}
