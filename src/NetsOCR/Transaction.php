<?php
  
namespace NetsOCR;

class Transaction {
  private $FormatKode;
  private $TjenesteKode;
  private $TransaksjonsType;
  private $RecordType;
  private $TransaksjonNummer;
  private $OppgjorsDato;
  private $SentralId;
  private $DagKode;
  private $DelAvregningsNummer;
  private $LopeNummer;
  private $Fortegn;
  private $Belop;
  private $Kid;
  private $KortUtsteder;
  private $BlankettNummer;
  private $AvtaleId;
  private $OppdragsDato;
  private $DebetKonto;
  private $FritekstMessage = '';

  public function setValue($key, $value) {
    if (!property_exists($this, $key)) {
      throw new \Exception('Unknown key: ' . $key);
    }
    $this->{$key} = $value;
  }
  public function getValue($key) {
    if (!property_exists($this, $key)) {
      throw new \Exception('Unknown key: ' . $key);
    }
    return $this->{$key};
  }
}
