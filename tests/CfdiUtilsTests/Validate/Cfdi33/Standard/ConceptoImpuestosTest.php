<?php
namespace CfdiUtilsTests\Validate\Cfdi33\Standard;

use CfdiUtils\Elements\Cfdi33\Comprobante;
use CfdiUtils\Nodes\Node;
use CfdiUtils\Validate\Cfdi33\Standard\ConceptoImpuestos;
use CfdiUtils\Validate\Status;
use CfdiUtilsTests\Validate\ValidateTestCase;

class ConceptoImpuestosTest extends ValidateTestCase
{
    /** @var ConceptoImpuestos */
    protected $validator;

    protected function setUp()
    {
        parent::setUp();
        $this->validator = new ConceptoImpuestos();
    }

    public function testInvalidCaseNoRetencionOrTraslado()
    {
        $comprobante = $this->validComprobante();
        $comprobante->addConcepto()->addChild(new Node('cfdi:Impuestos'));
        $this->runValidate();
        $this->assertStatusEqualsCode(Status::error(), 'CONCEPIMPC01');
    }

    public function providerInvalidBaseTraslado()
    {
        return[
            ['0'],
            ['0.0000001'],
            ['-1'],
            ['foo'],
            ['0.0.0.0'],
        ];
    }

    /**
     * @param $base
     * @dataProvider providerInvalidBaseTraslado
     */
    public function testTrasladoHasBaseGreaterThanZeroInvalidCase($base)
    {
        $comprobante = $this->validComprobante();
        $comprobante->addConcepto()->addTraslado(['Base' => $base]);
        $this->runValidate();
        $this->assertStatusEqualsCode(Status::error(), 'CONCEPIMPC02');
    }

    public function testTrasladosTipoFactorInvalidCase()
    {
        $comprobante = $this->validComprobante();
        $comprobante->addConcepto()->addTraslado([
            'TipoFactor' => 'Exento',
            'TasaOCuota' => '1',
        ]);
        $this->runValidate();
        $this->assertStatusEqualsCode(Status::error(), 'CONCEPIMPC03');
    }

    public function providerTrasladosTipoFactorTasaOCuotaInvalidCase()
    {
        return $this->providerFullJoin(
            [['Tasa'], ['Cuota']],
            [[''], [null]],
            [[''], [null]]
        );
    }

    /**
     * @param $tipoFactor
     * @param $tasaOCuota
     * @param $importe
     * @dataProvider providerTrasladosTipoFactorTasaOCuotaInvalidCase
     */
    public function testTrasladosTipoFactorTasaOCuotaInvalidCase($tipoFactor, $tasaOCuota, $importe)
    {
        $comprobante = $this->validComprobante();
        $comprobante->addConcepto()->addTraslado([
            'TipoFactor' => $tipoFactor,
            'TasaOCuota' => $tasaOCuota,
            'Importe' => $importe,
        ]);
        $this->runValidate();
        $this->assertStatusEqualsCode(Status::error(), 'CONCEPIMPC04');
    }

    public function providerInvalidBaseRetencion()
    {
        return[
            ['0'],
            ['0.0000001'],
            ['-1'],
            ['foo'],
            ['0.0.0.0'],
        ];
    }

    /**
     * @param $base
     * @dataProvider providerInvalidBaseTraslado
     */
    public function testRetencionesHasBaseGreaterThanZeroInvalidCase($base)
    {
        $comprobante = $this->validComprobante();
        $comprobante->addConcepto()->addRetencion(['Base' => $base]);
        $this->runValidate();
        $this->assertStatusEqualsCode(Status::error(), 'CONCEPIMPC05');
    }

    public function testInvalidCaseRetencionTipoFactorExento()
    {
        $comprobante = $this->validComprobante();
        $comprobante->addConcepto()->addRetencion(['TipoFactor' => 'Exento']);
        $this->runValidate();
        $this->assertStatusEqualsCode(Status::error(), 'CONCEPIMPC06');
    }

    public function testValidComprobante()
    {
        $this->validComprobante();
        $this->runValidate();
        $this->assertFalse($this->asserts->hasErrors());
    }

    private function validComprobante(): Comprobante
    {
        /** @var Comprobante $comprobante */
        $comprobante = $this->comprobante;
        $comprobante->addConcepto();
        $comprobante->addConcepto()->multiTraslado([
            'TipoFactor' => 'Exento',
            'Base' => '123.45',
        ], [
            'Base' => '123.45',
            'TipoFactor' => 'Tasa',
            'TasaOCuota' => '0.160000',
            'Importe' => '19.75',
        ])->multiRetencion([
            'Base' => '0.000001',
            'TipoFactor' => 'Tasa',
            'TasaOCuota' => '0.02',
            'Importe' => '1.23',
        ], [
            'Base' => '123.45',
            'TipoFactor' => 'Cuota',
        ]);
        return $comprobante;
    }
}
