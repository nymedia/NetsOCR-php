<?php
  
namespace NetsOCR;

class Parser {

  public $transactions = array();
  
  function parsePrice($price) {
    //return number_format(($price / 100), 2);
    return intval($price);
    
  }
  function __construct() {
    
  }

  function parseStartRecordForsendelse ($line) {
    $startRecordForsendelse = array(
      'FormatKode' => substr($line, 0, 2),
      'TjenesteKode' => substr($line, 2, 2),
      'ForsendelsesType' => substr($line, 4, 2),
      'RecordType' => substr($line, 6, 2),
      'DataAvsender' => substr($line, 8, 8),
      'ForsendelsesNummer' => substr($line, 16, 7),
      'DataMottaker' => substr($line, 23, 8),
      'Filler' => substr($line, 31),
    );
    return $startRecordForsendelse;
  }

  function parseStartRecordOppdrag ($line) {
    $startRecordOppdrag = array(
      'FormatKode' => substr($line, 0, 2),
      'TjenesteKode' => substr($line, 2, 2),
      'OppdragsYype' => substr($line, 4, 2),
      'RecordType' => substr($line, 6, 2),
      'AvtaleId' => substr($line, 8, 9),
      'Oppdragsnummer' => substr($line, 17, 7),
      'OppdragsKonto' => substr($line, 24, 11),
      'Filler' => substr($line, 35),
    );
    return $startRecordOppdrag;
  }
  function parseSluttRecordOppdrag ($line) {
    $SluttRecordOppdrag = array(
      'FormatKode' => substr($line, 0, 2),
      'TjenesteKode' => substr($line, 2, 2),
      'OppdragsType' => substr($line, 4, 2),
      'RecordType' => substr($line, 6, 2),
      'AntallTransaksjoner' => intval(substr($line, 8, 8)),
      'AntallRecords' => intval(substr($line, 16, 8)),
      'SumBelop' => $this->parsePrice(substr($line, 24, 17)),
      'OppgjorsDato' => substr($line, 41, 6),
      'ForsteOppgjorsdato' => substr($line, 47, 6),
      'SisteOppgjorsdato' => substr($line, 53, 6),
      'Filler' => substr($line, 59),
    );
    return $SluttRecordOppdrag;
  }
  function parseSluttRecordForsendelse ($line) {
    $SluttRecordForsendelse = array(
      'FormatKode' => substr($line, 0, 2),
      'TjenesteKode' => substr($line, 2, 2),
      'ForsendelsesType' => substr($line, 4, 2),
      'RecordType' => substr($line, 6, 2),
      'AntallTransaksjoner' => intval(substr($line, 8, 8)),
      'AntallRecords' => intval(substr($line, 16, 8)),
      'SumBelop' => $this->parsePrice(substr($line, 24, 17)),
      'OppgjorsDato' => substr($line, 41, 6),
      'Filler' => substr($line, 47),
    );
    return $SluttRecordForsendelse;
  }
  function parseTransactionLine1 (Transaction $transaction, $line) {
    $transaction->setValue('FormatKode', substr($line, 0, 2));
    $transaction->setValue('TjenesteKode', substr($line, 2, 2));
    $transaction->setValue('TransaksjonsType', substr($line, 4, 2));
    $transaction->setValue('RecordType', substr($line, 6, 2));
    $transaction->setValue('TransaksjonNummer', substr($line, 8, 7));
    $transaction->setValue('OppgjorsDato', substr($line, 15, 6));
    $transaction->setValue('SentralId', substr($line, 21, 2));
    $transaction->setValue('DagKode', substr($line, 23, 2));
    $transaction->setValue('DelAvregningsNummer', substr($line, 25, 1));
    $transaction->setValue('LopeNummer', substr($line, 26, 5));
    $transaction->setValue('Fortegn', substr($line, 31, 1));
    $transaction->setValue('Belop', $this->parsePrice(substr($line, 32, 17)));
    $transaction->setValue('Kid', ltrim(substr($line, 49, 25)));
    $transaction->setValue('KortUtsteder', substr($line, 74, 2));

    if (strlen(substr($line, 76)) !== 5) {
      throw new \Exception('Invalid line length line 1');
    }
  }

  function parseTransactionLine2(Transaction $transaction, $line) {
    $transaction->setValue('FormatKode', substr($line, 0, 2));
    $transaction->setValue('TjenesteKode', substr($line, 2, 2));
    $transaction->setValue('TransaksjonsType', substr($line, 4, 2));
    $transaction->setValue('RecordType', substr($line, 6, 2));
    $transaction->setValue('TransaksjonNummer', substr($line, 8, 7));
    $transaction->setValue('BlankettNummer', substr($line, 15, 10));
    $transaction->setValue('AvtaleId', substr($line, 25, 9));
    $transaction->setValue('OppdragsDato', substr($line, 41, 6));
    $transaction->setValue('DebetKonto', substr($line, 47, 11));

    if (strlen(substr($line, 58)) !== 23) {
      throw new \Exception('Invalid line length line 2');
    }
  }

  function parseTransactionLine3(Transaction $transaction, $line) {
    $transaction->setValue('FormatKode', substr($line, 0, 2));
    $transaction->setValue('TjenesteKode', substr($line, 2, 2));
    $transaction->setValue('TransaksjonsType', substr($line, 4, 2));
    $transaction->setValue('RecordType', substr($line, 6, 2));
    $transaction->setValue('TransaksjonNummer', substr($line, 8, 7));
    $transaction->setValue('FritekstMessage', substr($line, 16, 40));

    if (strlen(substr($line, 56)) !== 25) {
      throw new \Exception('Invalid line length line 3');
    }
  }

  function parseTransactions ($lines) {
    
    $line_counter = 1;

    foreach ($lines as $line) {
      if ($line_counter == 1) {
        $this_transaction = new Transaction();
        $this->parseTransactionLine1($this_transaction, $line);
        $line_counter++;
      }
      elseif ($line_counter == 2) {
        $this->parseTransactionLine2($this_transaction, $line);
        if ($this_transaction->getValue('TransaksjonsType') == '20' || $this_transaction->getValue('TransaksjonsType') == '21') {
          $line_counter++;
        }
        else {
          $line_counter = 1;
          $this->transactions[] = $this_transaction;
        }
      }
      elseif ($line_counter == 3) {
        $this->parseTransactionLine3($this_transaction, $line);
        $line_counter = 1;
      }
      else {
        throw new \Exception('Unknown transaction line');
      }
    }
  }
  
  public function getTransactions() {
    return $this->transactions;
  }

  /**
   * Parse each line and build an array of the content.
   *
   * @param $string
   *  The OCR content.
   *
   * @return array
   *  Structured array of file content.
   */
  public function parse($string) {
    $lines = explode("\n", $string);

    // Remove the last empty line.
    $last_item = array_pop($lines);
    if ($last_item !== '') {
      $lines[] = $last_item;
    }

    $this->parseTransactions(array_slice($lines, 2, -2, TRUE));

    $output = array(
      'StartRecordForsendelse' => $this->parseStartRecordForsendelse($lines[0]),
      'StartRecordOppdrag' => $this->parseStartRecordOppdrag($lines[1]),
      'Transactions' => $this->getTransactions(),
      'SluttRecordOppdrag' => $this->parseSluttRecordOppdrag($lines[count($lines) - 2]),
      'SluttRecordForsendelse' => $this->parseSluttRecordForsendelse($lines[count($lines) - 1]),
    );
    return $output;
  }

  public function parseFile($filePath) {
    $ocr_file = file_get_contents($filePath);

    return $this->parse($ocr_file);
  }
}
