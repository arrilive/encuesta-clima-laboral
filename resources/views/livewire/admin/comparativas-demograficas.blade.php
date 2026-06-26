<div class="bg-white rounded-2xl shadow-sm p-4 mt-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="text-base font-semibold text-slate-800 tracking-tight mb-1">Comparativas demográficas</h2>
            <p class="text-slate-500 text-sm">Puntaje promedio en cada dimensión según el grupo seleccionado</p>
        </div>
        <select wire:model.live="campoComparativa"
            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm min-w-[200px]
                   hover:border-blue-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10
                   transition-colors cursor-pointer">
            <option value="sexo">Sexo</option>
            <option value="cargo">Cargo</option>
            <option value="edad">Edad</option>
            <option value="antiguedad">Antigüedad</option>
            <option value="lugar_trabajo">Lugar de trabajo</option>
            <option value="grado_academico">Grado académico</option>
        </select>
    </div>

    <div x-data="{ chart: null }" x-init="let initialData = @js($comparativas);
    if (chart) { chart.destroy(); }
    let opts = { ...window.comparativasOptions };
    opts.series = initialData.series;
    opts.xaxis = { ...opts.xaxis, categories: initialData.categorias };
    chart = new ApexCharts($el.querySelector('#comparativas-chart'), opts);
    chart.render();"
        x-on:comparativas-actualizadas.window="
            if (chart) { chart.destroy(); }
            let opts = { ...window.comparativasOptions };
            opts.series = $event.detail.comparativas.series;
            opts.xaxis = { ...opts.xaxis, categories: $event.detail.comparativas.categorias };
            chart = new ApexCharts($el.querySelector('#comparativas-chart'), opts);
            chart.render();
        ">
        <div x-ignore>
            <div id="comparativas-chart" style="min-height: 400px"></div>
        </div>
    </div>

    @script
        <script>
            window.comparativasDatos = @json($comparativas);

            window.comparativasOptions = {
                chart: {
                    type: 'bar',
                    height: 400,
                    fontFamily: 'DM Sans, sans-serif',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 4,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: false,
                    formatter: val => val.toFixed(1),
                    offsetY: -20,
                    style: {
                        fontSize: '10px',
                        colors: ['#64748b']
                    }
                },
                series: window.comparativasDatos.series,
                xaxis: {
                    categories: window.comparativasDatos.categorias,
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    tickAmount: 5,
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px'
                        },
                        formatter: val => Math.round(val)
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '13px',
                    fontFamily: 'DM Sans, sans-serif',
                    markers: {
                        radius: 12
                    }
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: val => val.toFixed(1) + ' pts'
                    }
                }
            };

            $wire.on('comparativas-actualizadas', ({
                comparativas
            }) => {
                window.dispatchEvent(new CustomEvent('comparativas-actualizadas', {
                    detail: {
                        comparativas
                    }
                }));
            });
        </script>
    @endscript
</div>
