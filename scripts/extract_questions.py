#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para extraer preguntas de la encuesta de clima laboral desde Excel
Genera archivos JSON estructurados listos para los seeders de Laravel
"""

import pandas as pd
import json
from pathlib import Path

# Configuración
EXCEL_FILE = 'storage/app/imprimir Clima_laboral_final.xlsx'
OUTPUT_DIR = 'storage/app/extracted_data'

# Correcciones ortográficas predefinidas
CORRECCIONES = {
    'espectativas': 'expectativas',
    'esun': 'es un',
    'fisicamente': 'físicamente',
    'psicologica': 'psicológica',
    'academico': 'académico',
    'posicion': 'posición',
    'desiciones': 'decisiones',
    'ganancias': 'ganancias',
    'Jefes': 'jefes',
    'Antiquedad': 'Antigüedad',
}

def corregir_ortografia(texto):
    """Aplica correcciones ortográficas al texto"""
    if pd.isna(texto):
        return texto
    
    texto_corregido = str(texto)
    for error, correccion in CORRECCIONES.items():
        texto_corregido = texto_corregido.replace(error, correccion)
    
    return texto_corregido.strip()

def extraer_dimensiones_y_preguntas():
    """
    Extrae todas las dimensiones, subdimensiones y preguntas del Excel
    """
    print("="*80)
    print("EXTRAYENDO PREGUNTAS DEL EXCEL")
    print("="*80)
    
    # Leer Excel
    df = pd.read_excel(EXCEL_FILE, sheet_name=0)
    
    # Estructura de datos
    dimensiones = []
    
    # DIMENSIÓN 1: CREDIBILIDAD (16 preguntas)
    dimensiones.append({
        'nombre': 'Credibilidad',
        'orden': 1,
        'descripcion': 'Mide la confianza en la dirección de la empresa',
        'subdimensiones': [
            {
                'nombre': 'Comunicación',
                'orden': 1,
                'preguntas': [
                    '¿Los jefes comunican claramente sus expectativas?',
                    '¿Puedo hacer a los jefes cualquier pregunta razonable y recibir una respuesta clara?',
                    '¿Los jefes son accesibles y es fácil hablar con ellos?',
                    '¿Los jefes me mantienen informado acerca de asuntos y cambios importantes?'
                ]
            },
            {
                'nombre': 'Capacidad',
                'orden': 2,
                'preguntas': [
                    '¿Los jefes confían que los colaboradores hacen un buen trabajo sin supervisarlos continuamente?',
                    '¿Los jefes asignan y coordinan bien al personal?',
                    '¿La gente conoce los objetivos y metas en su trabajo?',
                    '¿Los jefes manejan el negocio de forma competente?',
                    '¿Los jefes contratan gente de acuerdo a la cultura de la empresa?',
                    '¿Los jefes promueven el trabajo en equipo?',
                    '¿A la gente le delegan responsabilidades?'
                ]
            },
            {
                'nombre': 'Integridad',
                'orden': 3,
                'preguntas': [
                    '¿Los jefes dirigen el negocio de una manera honesta y ética?',
                    '¿Los valores de la empresa son practicados por todos?',
                    '¿Los jefes cumplen sus promesas?',
                    '¿Las palabras de los jefes coinciden con sus acciones?',
                    '¿Crees que la empresa despediría a las personas como última opción?'
                ]
            }
        ]
    })
    
    # DIMENSIÓN 2: RESPETO (14 preguntas)
    dimensiones.append({
        'nombre': 'Respeto',
        'orden': 2,
        'descripcion': 'Mide el apoyo, valoración y colaboración hacia los colaboradores',
        'subdimensiones': [
            {
                'nombre': 'Apoyo',
                'orden': 1,
                'preguntas': [
                    '¿Me dan los recursos y herramientas necesarias para hacer mi trabajo?',
                    '¿Me ofrecen capacitación y otro tipo de desarrollo para apoyar mi crecimiento profesional?',
                    '¿Los jefes reconocen el trabajo bien hecho y el esfuerzo extra?',
                    '¿Los jefes reconocen que pueden cometerse errores involuntarios al hacer el trabajo?',
                    '¿En esta empresa existen buenas oportunidades de crecimiento?'
                ]
            },
            {
                'nombre': 'Valoración',
                'orden': 2,
                'preguntas': [
                    '¿Este es un lugar físicamente seguro para trabajar?',
                    '¿Este es un lugar psicológica y emocionalmente saludable para trabajar?',
                    '¿Las instalaciones contribuyen a un buen ambiente de trabajo?',
                    '¿A las personas se les anima a que equilibren su vida laboral y su vida personal?',
                    '¿Tenemos beneficios especiales y únicos en esta empresa?',
                    '¿Los jefes demuestran su interés sincero en mí como persona, no solo como empleado?',
                    '¿Cuando es necesario puedo ausentarme para atender asuntos personales durante el horario de trabajo?'
                ]
            },
            {
                'nombre': 'Colaboración',
                'orden': 3,
                'preguntas': [
                    '¿Los jefes involucran a la gente en decisiones que afecten su trabajo o su ambiente laboral?',
                    '¿Los jefes fomentan y responden genuinamente a nuestras sugerencias e ideas?'
                ]
            }
        ]
    })
    
    # DIMENSIÓN 3: IMPARCIALIDAD (12 preguntas)
    dimensiones.append({
        'nombre': 'Imparcialidad',
        'orden': 3,
        'descripcion': 'Mide la equidad y justicia en el trato a los colaboradores',
        'subdimensiones': [
            {
                'nombre': 'Equidad',
                'orden': 1,
                'preguntas': [
                    '¿Todos tenemos la oportunidad de recibir un reconocimiento especial?',
                    '¿A los colaboradores se les paga justamente por el trabajo que realizan?',
                    '¿Siento que recibo una parte justa de las ganancias que obtiene la empresa?',
                    '¿Me tratan bien independientemente de mi posición en la empresa?',
                    '¿Se evalúa el desempeño de los colaboradores de manera justa?'
                ]
            },
            {
                'nombre': 'Ausencia de favoritismo',
                'orden': 2,
                'preguntas': [
                    '¿Los jefes evitan tener empleados favoritos?',
                    '¿Los ascensos se dan a quienes más lo merecen?',
                    '¿Los colaboradores evitan hacer "grilla" para obtener un beneficio personal?'
                ]
            },
            {
                'nombre': 'Justicia',
                'orden': 3,
                'preguntas': [
                    '¿La gente es tratada justamente sin importar su edad?',
                    '¿La gente es tratada justamente sin importar su sexo?',
                    '¿La gente es tratada justamente sin importar su preferencia sexual?',
                    '¿Si soy tratado injustamente, sé que tendré oportunidad de ser escuchado y recibir un trato justo?'
                ]
            }
        ]
    })
    
    # DIMENSIÓN 4: ORGULLO (8 preguntas)
    dimensiones.append({
        'nombre': 'Orgullo',
        'orden': 4,
        'descripcion': 'Mide el sentido de pertenencia y orgullo por el trabajo',
        'subdimensiones': [
            {
                'nombre': 'Del Equipo',
                'orden': 1,
                'preguntas': [
                    '¿Los colaboradores están dispuestos a hacer un esfuerzo extra para realizar el trabajo?',
                    '¿Cuando veo lo que logramos, me siento orgulloso?'
                ]
            },
            {
                'nombre': 'Del Trabajo',
                'orden': 2,
                'preguntas': [
                    '¿Mi trabajo tiene un significado especial; "Para mí este no solo es un trabajo"?',
                    '¿Siento que mi participación hace una diferencia en la organización?'
                ]
            },
            {
                'nombre': 'De la Empresa',
                'orden': 3,
                'preguntas': [
                    '¿Me siento bien por la forma en la que contribuimos a la sociedad?',
                    '¿A la gente le gusta venir a trabajar aquí?',
                    '¿Estoy orgulloso de decirle a otros que trabajo aquí?',
                    '¿Deseo trabajar aquí por un largo tiempo?'
                ]
            }
        ]
    })
    
    # DIMENSIÓN 5: COMPAÑERISMO (9 preguntas)
    dimensiones.append({
        'nombre': 'Compañerismo',
        'orden': 5,
        'descripcion': 'Mide la calidad de las relaciones entre colaboradores',
        'subdimensiones': [
            {
                'nombre': 'Hospitalidad',
                'orden': 1,
                'preguntas': [
                    '¿Este es un lugar amigable para trabajar?',
                    '¿Este es un lugar donde se disfruta trabajar?'
                ]
            },
            {
                'nombre': 'Cercanía',
                'orden': 2,
                'preguntas': [
                    '¿Puedo ser yo mismo aquí?',
                    '¿Aquí las personas se preocupan por los demás?',
                    '¿Aquí las personas celebran eventos especiales?'
                ]
            },
            {
                'nombre': 'Sentido de Familia',
                'orden': 3,
                'preguntas': [
                    '¿Puedo contar con la ayuda de las personas?',
                    '¿Aquí hay un sentido de familia o equipo?',
                    '¿Estamos todos juntos en esto?',
                    '¿Tomando en consideración todas las preguntas, yo diría que este es un lugar excelente para trabajar?'
                ]
            }
        ]
    })
    
    # DIMENSIÓN 6: SEGURIDAD Y CAPACITACIÓN (14 preguntas)
    dimensiones.append({
        'nombre': 'Seguridad y Capacitación',
        'orden': 6,
        'descripcion': 'Mide la seguridad física, emocional y capacitación recibida',
        'subdimensiones': [
            {
                'nombre': 'Seguridad',
                'orden': 1,
                'preguntas': [
                    '¿El programa de prevención de accidentes y riesgos de trabajo es adecuado?',
                    '¿La empresa está haciendo lo posible por disminuir los riesgos?',
                    '¿Me siento apoyado por la empresa ante los riesgos que tiene mi trabajo?',
                    '¿La tarea que tengo asignada tiene riesgos de accidentes?',
                    '¿La tarea que tengo asignada es segura y no tiene riesgos?',
                    '¿Tu trabajo exige que estés muy concentrado?',
                    '¿Tu trabajo exige que atiendas varios asuntos a la vez?',
                    '¿Por la cantidad de trabajo que tienes debes trabajar sin parar?',
                    '¿Consideras que es necesario mantener un ritmo de trabajo acelerado?',
                    '¿Tengo la impresión que mi vida se va cortando?',
                    '¿Empiezo a sentir que mi existencia pierde sentido, y esa sensación me pesa cada día un poco más?'
                ]
            },
            {
                'nombre': 'Capacitación',
                'orden': 2,
                'preguntas': [
                    '¿Siento que he recibido la capacitación para el puesto que ocupo?',
                    '¿La capacitación es planeada de acuerdo a las necesidades de la empresa?',
                    '¿El programa de inducción y acondicionamiento para realizar el trabajo es adecuado?'
                ]
            }
        ]
    })
    
    return dimensiones

def extraer_preguntas_abiertas():
    """Extrae las 3 preguntas abiertas"""
    return [
        {
            'orden': 1,
            'pregunta': '¿Si pudieras cambiar algo acerca de tu empresa para hacerla un mejor lugar para trabajar, qué cambiarías?',
            'limite_caracteres': 500
        },
        {
            'orden': 2,
            'pregunta': '¿Existe algo especial o único en tu empresa que lo caracterice como un gran lugar para trabajar?',
            'limite_caracteres': 500
        },
        {
            'orden': 3,
            'pregunta': '¿A quién reconocerías como embajador/a de la cultura laboral de la empresa?',
            'limite_caracteres': 300
        }
    ]

def extraer_datos_demograficos():
    """Extrae las opciones de los datos demográficos"""
    return {
        'antiguedades': [
            {'orden': 1, 'opcion': '2 años o menos'},
            {'orden': 2, 'opcion': '3 a 5 años'},
            {'orden': 3, 'opcion': '6 a 10 años'},
            {'orden': 4, 'opcion': '11 a 15 años'},
            {'orden': 5, 'opcion': 'Más de 16 años'}
        ],
        'edades': [
            {'orden': 1, 'opcion': '25 años o menos'},
            {'orden': 2, 'opcion': '26 a 34 años'},
            {'orden': 3, 'opcion': '35 a 44 años'},
            {'orden': 4, 'opcion': '45 a 54 años'},
            {'orden': 5, 'opcion': '55 años o más'}
        ],
        'lugares_trabajo': [
            {'orden': 1, 'opcion': 'Corporativo'},
            {'orden': 2, 'opcion': 'Sucursal'}
        ],
        'sexos': [
            {'orden': 1, 'opcion': 'Mujer'},
            {'orden': 2, 'opcion': 'Hombre'}
        ],
        'grados_academicos': [
            {'orden': 1, 'opcion': 'Preparatoria trunca'},
            {'orden': 2, 'opcion': 'Preparatoria / carrera técnica'},
            {'orden': 3, 'opcion': 'Licenciatura trunca'},
            {'orden': 4, 'opcion': 'Licenciatura / Ingeniería'},
            {'orden': 5, 'opcion': 'Post grado'}
        ],
        'cargos': [
            {'orden': 1, 'opcion': 'Director'},
            {'orden': 2, 'opcion': 'Gerente o subgerente'},
            {'orden': 3, 'opcion': 'Jefe de área'},
            {'orden': 4, 'opcion': 'Administrativo'},
            {'orden': 5, 'opcion': 'Operativo'}
        ]
    }

def main():
    """Función principal"""
    print("\n🚀 Iniciando extracción de preguntas...\n")
    
    # Crear directorio de salida
    output_path = Path(OUTPUT_DIR)
    output_path.mkdir(parents=True, exist_ok=True)
    
    # Extraer dimensiones y preguntas
    print("📊 Extrayendo dimensiones y preguntas de clima laboral...")
    dimensiones = extraer_dimensiones_y_preguntas()
    
    # Contar preguntas
    total_preguntas = 0
    for dim in dimensiones:
        for subdim in dim['subdimensiones']:
            total_preguntas += len(subdim['preguntas'])
    
    print(f"   ✅ {len(dimensiones)} dimensiones extraídas")
    print(f"   ✅ {total_preguntas} preguntas cerradas extraídas")
    
    # Extraer preguntas abiertas
    print("\n📝 Extrayendo preguntas abiertas...")
    preguntas_abiertas = extraer_preguntas_abiertas()
    print(f"   ✅ {len(preguntas_abiertas)} preguntas abiertas extraídas")
    
    # Extraer datos demográficos
    print("\n👥 Extrayendo opciones de datos demográficos...")
    demograficos = extraer_datos_demograficos()
    total_opciones = sum(len(opciones) for opciones in demograficos.values())
    print(f"   ✅ {total_opciones} opciones demográficas extraídas")
    
    # Guardar archivos JSON
    print("\n💾 Guardando archivos JSON...")
    
    # Dimensiones y preguntas
    with open(output_path / 'dimensiones_preguntas.json', 'w', encoding='utf-8') as f:
        json.dump(dimensiones, f, ensure_ascii=False, indent=2)
    print(f"   ✅ {output_path / 'dimensiones_preguntas.json'}")
    
    # Preguntas abiertas
    with open(output_path / 'preguntas_abiertas.json', 'w', encoding='utf-8') as f:
        json.dump(preguntas_abiertas, f, ensure_ascii=False, indent=2)
    print(f"   ✅ {output_path / 'preguntas_abiertas.json'}")
    
    # Datos demográficos
    with open(output_path / 'datos_demograficos.json', 'w', encoding='utf-8') as f:
        json.dump(demograficos, f, ensure_ascii=False, indent=2)
    print(f"   ✅ {output_path / 'datos_demograficos.json'}")
    
    print("\n" + "="*80)
    print("✅ EXTRACCIÓN COMPLETADA")
    print("="*80)
    print(f"\n📊 Resumen:")
    print(f"   - Dimensiones: {len(dimensiones)}")
    print(f"   - Subdimensiones: {sum(len(d['subdimensiones']) for d in dimensiones)}")
    print(f"   - Preguntas cerradas: {total_preguntas}")
    print(f"   - Preguntas abiertas: {len(preguntas_abiertas)}")
    print(f"   - Opciones demográficas: {total_opciones}")
    print(f"\n📁 Archivos generados en: {output_path}")
    print("\n🎯 Siguiente paso: Revisar los archivos JSON y validar las preguntas\n")

if __name__ == '__main__':
    main()