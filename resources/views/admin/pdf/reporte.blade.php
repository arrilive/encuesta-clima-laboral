<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Clima Laboral</title>
    <style>
        body {
            font-family: sans-serif;
            color: #334155;
            font-size: 13px;
            margin: 0;
            padding: 20px;
        }

        h1,
        h2,
        h3 {
            color: #0f172a;
            margin-top: 0;
        }

        .header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .header h1 {
            color: #2563EB;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .meta-info {
            font-size: 12px;
            color: #64748b;
        }

        .filtros-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-top: 15px;
            font-size: 11px;
        }

        .filtros-box strong {
            color: #0f172a;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-excelente {
            background-color: #d1fae5;
            color: #059669;
        }

        .badge-bueno {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .badge-atencion {
            background-color: #fef3c7;
            color: #d97706;
        }

        .badge-riesgo {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 18px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
        }

        td {
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .kpi-container {
            width: 100%;
            margin-bottom: 25px;
        }

        .kpi-box {
            float: left;
            width: 48%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .kpi-title {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .kpi-value {
            font-size: 26px;
            font-weight: bold;
            color: #0f172a;
        }

        .radar-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #ffffff;
        }

        .subdimension-group {
            margin-bottom: 25px;
            padding-bottom: 15px;
        }

        .subdimension-group-title {
            font-size: 15px;
            color: #2563EB;
            margin-bottom: 10px;
        }

        .abierta-grupo {
            margin-bottom: 30px;
        }

        .abierta-pregunta {
            font-size: 14px;
            color: #0f172a;
            font-weight: bold;
            margin-bottom: 15px;
            background-color: #f1f5f9;
            padding: 10px;
            border-radius: 4px;
        }

        .abierta-respuesta {
            font-size: 12px;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .abierta-respuesta:last-child {
            border-bottom: none;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>



    <div class="header">
        <h1>Reporte de Clima Laboral</h1>
        <div class="meta-info">
            Fecha de generación: {{ date('d/m/Y H:i') }} | Alcance: {{ ucfirst(str_replace('_', ' ', $alcance)) }}
        </div>

        @if (!empty($filtrosActivos))
            <div class="filtros-box">
                <strong>Filtros aplicados:</strong><br>
                @foreach ($filtrosActivos as $key => $value)
                    <span style="margin-right: 15px;">• {{ $key }}: {{ $value }}</span>
                @endforeach
            </div>
        @else
            <div class="filtros-box">
                <strong>Filtros aplicados:</strong> Todos los datos
            </div>
        @endif
    </div>
    <table width="100%" cellspacing="0" cellpadding="0"
        style="margin-bottom: 30px; border-collapse: separate; border: none;">
        <tr>
            <td width="48%" valign="top" style="padding: 0; border: none;">
                <div
                    style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; height: 95px;">
                    <div class="kpi-title">Promedio General</div>
                    <div class="kpi-value">{{ number_format($promedioGeneral, 1) }}</div>
                    <div style="margin-top: 8px;">
                        <span
                            class="badge {{ \App\Support\ClimaBadge::resolver($promedioGeneral)['pdf_class'] }}">{{ \App\Support\ClimaBadge::resolver($promedioGeneral)['label'] }}</span>
                    </div>
                </div>
            </td>
            <td width="4%" style="padding: 0; border: none;"></td>
            <td width="48%" valign="top" style="padding: 0; border: none;">
                <div
                    style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; height: 95px;">
                    <div class="kpi-title">Encuestas Completadas</div>
                    <div class="kpi-value">{{ $completadas }}</div>
                    <div style="margin-top: 8px; font-size: 12px; color: #64748b;">Participaciones válidas</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- SIEMPRE MOSTRAR BLOQUES --}}
    <div class="section">
        <h2 class="section-title">Resultados por Dimensión</h2>
        <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">Este apartado presenta el desempeño general
            agrupado por las principales dimensiones de la encuesta. Un puntaje cercano a 100 indica un clima laboral
            excelente en esa dimensión.</p>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Dimensión</th>
                    <th class="text-center">Puntaje</th>
                    <th>Interpretación</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($datosDimensiones as $index => $item)
                    <tr>
                        <td style="color: #64748b;">{{ $index + 1 }}</td>
                        <td style="font-weight: bold;">{{ $item['nombre'] }}</td>
                        <td class="text-center">
                            <strong style="color: #0f172a;">{{ number_format($item['puntaje'], 1) }}</strong>
                        </td>
                        <td>
                            <span class="badge {{ \App\Support\ClimaBadge::resolver($item['puntaje'])['pdf_class'] }}">
                                {{ \App\Support\ClimaBadge::resolver($item['puntaje'])['label'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px 0;">No hay datos suficientes para mostrar esta tabla.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    {{-- SECCIONES --}}
    @if (in_array($alcance, ['subdimensiones', 'completo']) && !empty($datosSubdimensiones))
        <div class="page-break"></div>
        <div class="section">
            <h2 class="section-title">Resultados por Subdimensión</h2>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">Desglose detallado de cada dimensión en sus
                distintas subdimensiones.</p>

            @foreach ($datosSubdimensiones as $dimensionNombre => $subdimensiones)
                <div class="subdimension-group">
                    <div class="subdimension-group-title">{{ $dimensionNombre }}</div>
                    <table>
                        <thead>
                            <tr>
                                <th width="35%">Subdimensión</th>
                                <th width="10%" class="text-center">Puntaje</th>
                                <th width="55%">Distribución</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subdimensiones as $sub)
                                <tr>
                                    <td style="font-size: 12px;">{{ $sub['nombre'] }}</td>
                                    <td class="text-center">
                                        <strong
                                            style="color: #0f172a;">{{ number_format($sub['puntaje'], 1) }}</strong>
                                    </td>
                                    <td>
                                        <div style="width: 250px; background-color: #e2e8f0; border-radius: 4px;">
                                            <div
                                                style="height: 12px; width: {{ max(0, min(100, (float) ($sub['puntaje'] ?? 0))) }}%; background-color: {{ \App\Support\ClimaBadge::resolver($sub['puntaje'] ?? 0)['color_hex'] }}; border-radius: 4px;">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    {{-- RESPUESTAS ABIERTAS --}}
    @if (in_array($alcance, ['respuestas_abiertas', 'completo']) && !empty($respuestasAbiertas))
        <div class="page-break"></div>
        <div class="section">
            <h2 class="section-title">Respuestas Abiertas</h2>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">
                Esta sección recopila las respuestas de la última parte de la encuesta, mostrando las <strong>últimas
                    {{ request('limite', 25) }} respuestas</strong>.
            </p>

            @foreach ($respuestasAbiertas as $item)
                <div class="abierta-grupo">
                    <div class="abierta-pregunta">{{ $item['pregunta'] }}</div>
                    @if (count($item['respuestas']) > 0)
                        @foreach ($item['respuestas'] as $respuesta)
                            <div class="abierta-respuesta">
                                "{{ $respuesta }}"
                            </div>
                        @endforeach
                    @else
                        <div class="abierta-respuesta" style="color: #94a3b8; font-style: italic;">
                            No hay respuestas registradas para esta pregunta bajo los filtros actuales.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</body>

</html>
