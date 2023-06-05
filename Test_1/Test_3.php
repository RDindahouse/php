<!DOCTYPE html>
<html>
<head>
    <title>Курсы валют</title>
    <style>
        table {
            border-collapse: collapse;
        }
        
        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>
<body>
    <?php
    echo "Подготовил Осипов П.В.<br>";
    echo "Резюме - https://hh.ru/resume/a8f9a84eff07dbbdb10039ed1f6d5575515376<br>";
    class CurrencyData {
        private $data;

        public function __construct() {
            $this->data = array();
        }

        public function fetchData() {
            $startDate = new DateTime();
            $endDate = new DateTime();
            $endDate->modify('-90 days');
            $currentDate = clone $endDate;

            while ($currentDate <= $startDate) {
                $date = $currentDate->format('d/m/Y');
                $url = "http://www.cbr.ru/scripts/XML_daily_eng.asp?date_req=$date";
                $xml = simplexml_load_file($url);

                foreach ($xml->Valute as $valute) {
                    $code = (string) $valute->CharCode;
                    $nominal = (int) $valute->Nominal;
                    $value = (float) str_replace(',', '.', $valute->Value);

                    if (!isset($this->data[$code])) {
                        $this->data[$code] = array(
                            'max' => array('value' => $value / $nominal, 'date' => $date),
                            'min' => array('value' => $value / $nominal, 'date' => $date),
                            'sum' => 0,
                            'count' => 0
                        );
                    } else {
                        $adjustedValue = $value / $nominal;

                        if ($adjustedValue > $this->data[$code]['max']['value']) {
                            $this->data[$code]['max']['value'] = $adjustedValue;
                            $this->data[$code]['max']['date'] = $date;
                        }

                        if ($adjustedValue < $this->data[$code]['min']['value']) {
                            $this->data[$code]['min']['value'] = $adjustedValue;
                            $this->data[$code]['min']['date'] = $date;
                        }
                    }

                    $this->data[$code]['sum'] += $value / $nominal;
                    $this->data[$code]['count']++;
                }

                $currentDate->modify('+1 day');
            }
        }

        public function getMaxValues() { 
            $result = array();

            foreach ($this->data as $code => $currency) {
                $result[$code] = array(
                    'max_value' => $currency['max']['value'],
                    'max_date' => $currency['max']['date']
                );
            }

            return $result;
        }

        public function getMinValues() { 
            $result = array();

            foreach ($this->data as $code => $currency) {
                $result[$code] = array(
                    'min_value' => $currency['min']['value'],
                    'min_date' => $currency['min']['date']
                );
            }

            return $result;
        }

        public function getAverageRates() { 
            $result = array();

            foreach ($this->data as $code => $currency) {
                $average = $currency['sum'] / $currency['count'];
                $result[$code] = $average;
            }

            return $result;
        }
    }

    $currencyData = new CurrencyData();
    $currencyData->fetchData();

    $maxValues = $currencyData->getMaxValues();
    $minValues = $currencyData->getMinValues();
    $averageRates = $currencyData->getAverageRates();

    echo "<table>";
    echo "<tr><th>Валюта</th><th>Максимальное значение</th><th>Дата максимального значения</th>";
    echo "<th>Минимальное значение</th><th>Дата минимального значения</th><th>Среднее значение</th></tr>";

    foreach ($maxValues as $code => $maxData) {
        $minData = $minValues[$code];
        $average = $averageRates[$code];

        echo "<tr>";
        echo "<td>$code</td>";
        echo "<td>{$maxData['max_value']} (за 1 $code)</td>";
        echo "<td>{$maxData['max_date']}</td>";
        echo "<td>{$minData['min_value']} (за 1 $code)</td>";
        echo "<td>{$minData['min_date']}</td>";
        echo "<td>$average (за 1 $code)</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    ?>
</body>
</html>