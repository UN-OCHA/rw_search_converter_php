<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SearchConverter extends TestCase
{

    public function testBasicPrimaryCountry(): void
    {
      $this->assertEquals(
        "https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=primary_country.id%3A13&query[operator]=AND",
        convertToAPI('https://reliefweb.int/updates?advanced-search=(PC13)', 'unocha-org')
      );
    }

    public function testDateRange(): void
    {
      $aurl = convertToAPI('https://reliefweb.int/updates?advanced-search=(PC13_DA20190801-20190829)', 'unocha-org');
      $aurls = [
        'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=date.created%3A%5B2019-08-01%20TO%202019-08-30%7D%20AND%20primary_country.id%3A13&query[operator]=AND',
        'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=primary_country.id%3A13+AND+date.created%3A%5B2019-08-01+TO+2019-08-29%7D&query[operator]=AND'
      ];
      $this->assertTrue(in_array($aurl, $aurls));
    }

    public function testFromDate(): void
    {
      $aurl = convertToAPI('https://reliefweb.int/updates?advanced-search=(PC13_DA20190801-)#content', 'unocha-org');
      $aurls = [
        'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=date.created%3A%3E%3D2019-08-01%20AND%20primary_country.id%3A13&query[operator]=AND',
        'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=primary_country.id%3A13+AND+date.created%3A%3E%3D2019-08-01&query[operator]=AND'
      ];
      $this->assertTrue(in_array($aurl, $aurls));
    }

    public function testToDate(): void
    {
      $aurl = convertToAPI('https://reliefweb.int/updates?advanced-search=(PC13_DA-20190801)#content', 'unocha-org');
      $aurls = [
        'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=date.created%3A%3C2019-08-02%20AND%20primary_country.id%3A13&query[operator]=AND',
        'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=primary_country.id%3A13+AND+date.created%3A%3C2019-08-01&query[operator]=AND'

      ];
      $this->assertTrue(in_array($aurl, $aurls));
    }

    public function testOrganization(): void
    {
      $aurl = convertToAPI('https://reliefweb.int/updates?advanced-search=(PC13_S1503)#content', 'unocha-org');
      $aurls = [
        'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=primary_country.id%3A13%20AND%20source.id%3A1503&query[operator]=AND',
        'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=primary_country.id%3A13+AND+source.id%3A1503&query[operator]=AND'
      ];
      $this->assertTrue(in_array($aurl, $aurls));
    }

    public function testSearchQuery() : void
    {
      $aurl = convertToAPI('https://reliefweb.int/updates?search=Test', 'unocha-org');
      $success = 'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=Test&query[operator]=AND';
      $this->assertEquals($aurl, $success);
    }

    public function testComplexQuery() : void
    {
      $aurl = convertToAPI('https://reliefweb.int/updates?primary_country=254&source=1503&format=12570&search=title%3A%22Global+Humanitarian+Overview%22+OR+title%3A%22Humanitarian+Funding+Update%22', 'unocha-org');
      $success = 'https://api.reliefweb.int/v1/reports?appname=unocha-org&profile=list&preset=latest&slim=1&query[value]=%28title%3A%22Global+Humanitarian+Overview%22+OR+title%3A%22Humanitarian+Funding+Update%22%29+AND+%28format.id%3A%2812570%29+AND+primary_country.id%3A254+AND+source.id%3A1503%29&query[operator]=AND';
      $this->assertEquals($aurl, $success);
    }
}
