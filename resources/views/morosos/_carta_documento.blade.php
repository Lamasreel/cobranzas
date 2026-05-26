<style>
    body {
        font-family: dejavusans;
        font-size: 11px;
    }


    .header {
        text-align: center;
    }

    .titulo {
        font-size: 16px;
        font-weight: bold;
    }

    .subtitulo {
        font-size: 10px;
    }

    .carta-titulo {
        font-size: 20px;
        font-weight: bold;
    }

    .linea {
        border-top: 2px solid black;
    }

    .derecha {
        text-align: right;
    }

    .negrita {
        font-weight: bold;
    }

    .subrayado {
        text-decoration: underline;
    }

    .texto {
        text-align: justify;
    }

    .firma {
        text-align: right;
    }

    .salto {
        page-break-after: always;
    }
</style>

@foreach($clientes as $index => $m)

<div class="carta">

    <div class="header">
        <div class="titulo">ESTUDIO DE COBRANZAS PREJURIDICAS</div>
        <div class="subtitulo">Alberdi 765 - Aguilares - Tel. 3865-386766</div>

        <div class="carta-titulo">CARTA DOCUMENTADA</div>
    </div>

    <div class="linea"></div>

    <div class="derecha">
    Aguilares, 22 de Mayo de 2026
    </div>

    <div style="font-weight: bold;">
        Señor/a:
        <span class="negrita subrayado">
                    @if($m->titular_garantia == 'TIT')
                            {{ strtoupper($m->nombre) }}
                        @else
                            {{ strtoupper($m->nombre) }} GARANTE DE: {{ strtoupper($m->nombre_titular ?? 'SIN TITULAR') }}
                    @endif
        </span>
    </div>

    <div style="font-weight: bold;">
        Domicilio:
        <span class="negrita subrayado">
        {{ strtoupper($m->calle) }} / {{ strtoupper($m->observaciones) }}
        </span>
    </div>

    <div class="texto">
        Le comunico que por mandato de Premier SA en 72 hs. le iniciaré
        acciones judiciales tendientes al embargo de sus bienes y/o sueldo,
        si en el plazo no cancela su deuda vencida.
    </div>


    <div class="negrita">
        Queda Ud. notificado.
    </div>

    <div class="firma">
        <img src="{{ base_path('assets/images/firma_abogado.png') }}" width="70">
    </div>

</div>

@if(($index + 1) % 2 == 0)
    <div class="salto"></div>
@endif

@endforeach