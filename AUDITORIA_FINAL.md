# Auditoría final de calidad y preparación de VOLARA

## 1. Estado de la revisión

Esta revisión se realizó sobre la aplicación local en XAMPP, servida por Apache en `http://localhost/volara-sistema-aerolineas/`. Se combinaron pruebas en navegador, revisión del código y validaciones locales. Los resultados describen únicamente las rutas, cuentas y condiciones que se pudieron probar; no constituyen una certificación de producción ni reemplazan la rúbrica oficial de la cátedra.

**Resultado general: PARCIAL.** En esta ronda se completó el flujo autenticado de reserva en desktop y mobile, se verificó la cancelación >72 horas, se restauró la BD al estado previo y se probó una carrera de dos sesiones. Quedan pendientes el acceso admin por falta de contraseña conocida, la cancelación <72 horas (no hay un vuelo local apropiado), la entrega/activación/recuperación real por email, una prueba de lector de pantalla y los validadores W3C sobre URL pública.

## 2. Funcionalidad

| Criterio | Resultado | Evidencia / alcance |
|---|---|---|
| Aplicación y tecnologías | ✅ Verificado | Se revisó la estructura PHP, MySQL/PDO, Bootstrap y JavaScript del proyecto. No se introdujeron React, Node, npm, Tailwind ni frameworks nuevos. |
| Búsqueda y resultados | ✅ Verificado | Se recorrió desde la interfaz para el vuelo local de prueba en 1280 × 900 y 390 × 844. |
| Reserva y cancelación >72 h | ✅ Verificado | Cinco reservas temporales se crearon y cancelaron en local; cada cancelación restauró asiento/disponibilidad. Todos sus registros temporales se eliminaron al terminar. |
| Cancelación <72 h | ⚠️ Pendiente | Los vuelos programados disponibles no incluían una salida futura dentro de 72 horas; no se alteraron fechas de vuelos existentes. |
| Consistencia/contención | ✅ Parcialmente probado | Dos sesiones HTTP independientes intentaron reservar el mismo asiento: una creó reserva y la otra fue rechazada. Se comprobó el rechazo transaccional de una etiqueta de asiento inconsistente; no se inyectó un fallo SQL después de escrituras parciales. |
| Autorización por rol | ⚠️ Parcial | CEO se validó dinámicamente en la fase anterior; pasajero se autenticó con cuenta desechable. Admin no se probó porque el hash de la cuenta existente no permite recuperar una contraseña conocida. |
| SMTP real | ✅ Conexión/envío aceptado | PHPMailer envió un mensaje de prueba técnico al buzón configurado como remitente. No se comprobó su recepción ni el flujo real de registro/activación/recuperación. |

## 3. Usabilidad

| Criterio | Resultado | Evidencia / alcance |
|---|---|---|
| Identidad y consistencia visual | ✅ Corregido y revisado | La paleta de marca y los colores de estados se revisaron en los componentes afectados. La plantilla de activación también usa la identidad VOLARA. |
| Feedback de controles | ✅ Revisado | Los asientos disponibles, seleccionados y ocupados tienen estados visuales diferenciados; los ocupados se deshabilitan en la interacción revisada. |
| Rutas y contenido comprensibles | ✅ Parcialmente probado | Navegación, títulos y contenido visible fueron revisados en las rutas públicas y CEO de §14. No se declara que todas las tareas de todos los roles estén cubiertas. |
| Mapa del sitio | ✅ Revisado | Existe una página de mapa del sitio y los enlaces visibles se adaptan al rol de la sesión comprobada. |
| Ayuda, contacto y FAQ | ✅ Renderizado revisado | Las páginas públicas respondieron en las pruebas de navegador; no se enviaron mensajes de contacto ni correos. |

## 4. Accesibilidad

La revisión es manual y focalizada; no equivale a una auditoría WCAG ni a una prueba con tecnologías de asistencia.

### Perceptible

- Las páginas revisadas presentan títulos, labels y alternativas de texto observables en navegador.
- Se midieron en navegador pares de contraste específicos tras las correcciones: placeholder 4.83:1, rojo de marca sobre blanco 4.65:1, asiento disponible 5.01:1, asiento ocupado 6.62:1, badge de promoción 5.04:1 y badge cancelado 5.27:1. Son mediciones de esos pares, no de cada combinación de color del sistema.
- Se mejoró la distinción visual de asientos ocupados; su estado también se comunica por texto y estado accesible, no solo por color.

### Operable

- El enlace para saltar al contenido apunta a `<main id="contenido-principal" tabindex="-1">`; los 26 templates bajo `pages/` contienen ese destino.
- Se añadió el destino del skip link a login, registro, recuperación y restablecimiento de contraseña; los cuatro fueron comprobados en navegador.
- Por teclado se recorrieron skip link, marca, menú, formulario de búsqueda y selección de asiento; Enter abrió el menú y activó selección/asiento y continuación; Space deseleccionó el asiento. El submit inválido enfoca el primer campo con error.
- Se comprobó foco visible en skip link y botón toggler (outline sólido); el campo enfocado muestra borde de marca/sombra.
- El asiento ocupado de la página se encontró deshabilitado. Checkout y cancelación también se enviaron mediante Enter en el recorrido autenticado.
- No había controles de paginación en el resultado disponible (una coincidencia), por lo que la paginación no pudo ejercitarse.
- Las tablas anchas cuentan con regiones desplazables con nombre y foco por teclado.

### Comprensible

- Se revisaron idioma de documento, títulos y mensajes/labels en las rutas inspeccionadas.
- El nombre accesible del asiento cambia con su estado; el asiento ocupado anuncia ese estado.

### Robusta

- Se revisaron landmarks, labels y atributos ARIA en las páginas muestreadas.
- **Pendiente:** ensayo con NVDA, VoiceOver u otro lector de pantalla y validación automatizada completa.

## 5. Rapidez de acceso

| Criterio | Resultado | Evidencia / alcance |
|---|---|---|
| Navegación principal | ✅ Revisado | El encabezado incluye navegación y colapsa en los anchos probados. |
| Acceso desde teclado al contenido | ✅ Revisado | El skip link tiene destino en los 26 templates de `pages/`. |
| Velocidad de carga medida | ⚪ Pendiente | No se hizo una medición de laboratorio de carga ni de red; XAMPP local no representa producción. |

## 6. Diseño responsive

**Pasada final:** 78 combinaciones de ruta y viewport, sin respuesta distinta de HTTP 200, desbordamiento horizontal de página, destino de skip link ausente ni errores de consola/página observados.

| Superficie | Resultado | Alcance |
|---|---|---|
| Público | ✅ | 9 rutas × 6 anchos: 1920, 1440, 1024, 768, 480 y 375 px. |
| CEO | ✅ | 4 rutas × 6 anchos, en la sesión CEO disponible. |
| Menú | ✅ | Toggler probado por teclado a 390 px; la matriz anterior cubre además 1024 y 375 px. |
| Admin | ⚠️ | Falta una contraseña conocida y segura para una cuenta admin local de prueba. |
| Pasajero | ✅ | Se usó una cuenta temporal y luego se eliminaron cuenta y reservas de prueba. |
| Recorrido completo de reserva | ✅ | Búsqueda → resultados → detalle → asiento → confirmación → mis reservas en 1280 × 900 y 390 × 844; no hubo overflow. |

El menú se configuró para pasar al modo colapsado por debajo de XL. Las tablas de gestión/reportes tienen desplazamiento horizontal contenido, evitando ampliar el viewport de página.

## 7. HTML, CSS y estándares W3C

| Criterio | Resultado | Evidencia / alcance |
|---|---|---|
| Marcado básico | ✅ Comprobación focalizada | En nueve rutas públicas se revisaron encabezado principal, IDs duplicados, texto alternativo y destino del skip link; no se encontraron fallos en esas comprobaciones. |
| Referencias de recursos | ✅ Comprobación estática | El barrido de rutas locales `url()`/`asset()` no encontró recursos faltantes. |
| Validación formal HTML W3C | ⚠️ Pendiente | **Pendiente de ejecutar sobre URL pública.** La aplicación se probó solo en localhost; no se cargó HTML del proyecto a un servicio externo. |
| Validación formal CSS W3C | ⚠️ Pendiente | **Pendiente de ejecutar sobre URL pública.** No se declara aprobada. |
| Preparación local | ✅ Comprobación focalizada | En el navegador local, la hoja CSS cargó y expuso 214 reglas CSS parseadas; no es una validación W3C. Se corrigieron los destinos de skip link faltantes detectados. |

## 8. Seguridad

| Criterio | Resultado | Evidencia / alcance |
|---|---|---|
| Protección por rol y backend | ⚠️ Parcial | CEO tuvo prueba dinámica en la fase previa; pasajero se autenticó en esta ronda con cuenta temporal. Admin queda pendiente por falta de contraseña conocida. |
| Aislamiento CEO por aerolínea | ✅ En alcance probado | Las consultas CEO revisadas filtran por aerolínea y las pruebas dinámicas anteriores no permitieron acceder/modificar los registros de otra aerolínea en los casos intentados. |
| Aislamiento de reservas por pasajero | ⚠️ Parcial | Se revisó el filtro de propietario en código; no se pudo repetir la prueba dinámica de dos pasajeros con sesiones válidas en esta pasada. |
| Sentencias SQL | ✅ Revisión focalizada | Se inspeccionaron consultas críticas y su uso de parámetros PDO; esto no equivale a una auditoría exhaustiva de todas las consultas. |
| CSRF | ✅ Casos probados previamente | La validación de permisos anterior ejercitó el rechazo sin token en un formulario protegido. |
| Password hashing | ✅ Revisión de implementación | Se verificó el uso de `password_hash()`/`password_verify()` en el flujo de autenticación. |
| Sesión | ⚠️ Parcial | Se revisó regeneración/invalidez de sesión en código y se probó logout/bloqueo en la fase de permisos. No se declara completa la prueba de fijación/expiración con todas las cuentas. |
| `.env` | ✅ Verificado | El archivo local está ignorado por Git y no forma parte de los archivos versionados. No se muestra ni reproduce su contenido. |

## 9. Navegación

- Se revisó la navegación global y el mapa del sitio público en navegador.
- El menú CEO de la sesión probada mostró enlaces CEO y no mostró enlaces de administración.
- El skip link enlaza al landmark principal.
- **Pendiente:** recorrer y verificar todas las acciones con sesión admin válida; las rutas de pasajero del flujo de reserva se probaron en esta ronda.

## 10. Formularios

- Se revisaron labels, campos y validación en las pantallas incluidas en las rutas públicas/CEO probadas.
- Los formularios protegidos relevantes usan token CSRF según la revisión y prueba previa.
- No se envió el formulario de contacto. Búsqueda inválida, reserva y cancelación se probaron; flujos de registro/activación/recuperación por email quedan pendientes.
- No se exige validación mientras se escribe para considerar válida la validación server-side; los estados y mensajes deben probarse con entradas controladas antes de declarar el recorrido completo.

## 11. Errores y página 404

| Criterio | Resultado | Evidencia |
|---|---|---|
| Página no encontrada | ✅ | Una ruta inexistente servida por Apache respondió HTTP 404 y mostró la página VOLARA de recurso no encontrado. |
| Errores de JavaScript | ✅ En pasada responsive | No se observaron errores de consola ni excepciones de página en las 78 navegaciones de §14. |
| Errores PHP/SQL | ⚠️ Alcance limitado | No se observaron errores en las rutas dinámicas visitadas; no se inspeccionaron todos los logs del servidor ni se ejecutaron todos los formularios. |
| Rutas admin | ⚠️ | El recorrido protegido no se pudo probar dinámicamente sin credencial admin conocida. |
| Rutas de pasajero probadas | ✅ | El flujo autenticado de reserva/cancelación respondió sin errores PHP/JS visibles en las rutas recorridas. |

## 12. Branding y recursos visuales

- Las vistas comprobadas mantienen marca VOLARA y colores de marca coherentes.
- Se corrigió el favicon para declarar el tipo/tamaño PNG apropiado.
- Se añadió un derivado de logo de 256 × 256 para uso de pantalla; el original de mayor resolución se conserva.
- La plantilla del correo de activación usa colores de marca; no se verificó el renderizado en clientes de correo ni la entrega SMTP.
- El footer mantiene wordmark de texto; no se añadió el PNG de fondo blanco porque su presentación como logo pequeño sería visualmente inconsistente.

## 13. Rendimiento

| Criterio | Resultado | Evidencia / alcance |
|---|---|---|
| Logo mostrado | ✅ Optimización aplicada | Derivado PNG: 256 × 256, 40,407 bytes, frente al original de 1,254 × 1,254 y 820,110 bytes. Se conserva la fuente. |
| CSS actualizado | ✅ | La URL de la hoja de estilos incluye una versión basada en `filemtime()` para reducir falsos resultados por caché durante actualizaciones. |
| Tamaño de CSS/JS | ✅ Inspección básica | `styles.css` ≈33 KB y `main.js` ≈9.5 KB (≈42.6 KB en total); ambos cargan. No se aplicó minificación ni cambios sin un objetivo medido. |
| Assets duplicados | ✅ Inspección hash local | No se encontraron grupos con contenido SHA-256 duplicado. El PNG original y el derivado pequeño tienen dimensiones/contenido distintos. |
| Imagen de destino de gran tamaño | ⚠️ Observación, no defecto medido | La imagen mayor mide ≈1,293.5 KB; el logo fuente ≈800.9 KB. No se cambiaron porque no hay objetivo de calidad/tamaño ni medición de carga. |
| Consultas SQL repetidas | ⚠️ Sin trazado | No se ejecutó profiling de consultas ni análisis de carga; no se afirma que no haya consultas redundantes. |
| Métricas de carga | ⚠️ Pendiente de medición reproducible | No se ejecutaron Lighthouse/WebPageTest ni mediciones de red. |

## 14. Pruebas ejecutadas en esta revisión

| Prueba | Resultado | Evidencia |
|---|---|---|
| Navegación responsive pública | ✅ | 9 rutas × 6 viewports = 54 navegaciones; todas HTTP 200, sin overflow horizontal, skip link sin destino o errores JS observados. |
| Navegación responsive CEO | ✅ | 4 rutas × 6 viewports = 24 navegaciones con sesión CEO; mismas comprobaciones, sin fallos. |
| Total pasada final | ✅ | 78 navegaciones; consola y errores de página sin entradas. |
| Destino skip link | ✅ | En cada una de las 78 rutas visitadas, `href` coincidió con el ID del elemento `<main>`. La inspección estática encontró 26 templates con ese ID. |
| Título principal, IDs e imágenes | ✅ En muestra pública | Nueve rutas públicas revisadas; sin ID duplicados ni imágenes sin `alt` en la muestra. |
| Menú a tamaños reducidos | ✅ | Toggler expandido y operable a 1024 y 375 px. |
| Interacción de mapa de asientos | ✅ Prueba de interacción focalizada | Se probó con DOM mínimo: selección, cambio de asiento, deselección, estado accesible y bloqueo de asiento ocupado. No reemplaza una reserva real. |
| Contraste | ✅ Pares focalizados | Se midieron los seis pares listados en §4; no es un barrido completo. |
| 404 | ✅ | Respuesta HTTP 404 y contenido VOLARA comprobados con una ruta inexistente. |
| Sintaxis PHP | ✅ | `php -l` pasó en los 47 archivos PHP de aplicación fuera de `vendor/`. |
| Rutas de recursos | ✅ | Barrido estático encontró cero referencias locales faltantes en `url()`/`asset()`. |
| Estado de `.env` en Git | ✅ | Ignorado y no versionado; su valor no se inspeccionó ni divulga en este informe. |

## 15. Pruebas manuales pendientes

1. **Admin:** crear o proporcionar una cuenta de prueba admin únicamente en BD local aislada, con email único controlado y contraseña conocida; guardar la contraseña con `password_hash()`, y establecer `rol='admin'`, `activo=1`, `email_verificado=1`, `estado_aprobacion='aprobado'`. No promover ni cambiar usuarios reales. La contraseña del hash semilla no se puede recuperar.
2. **Cancelación <72 h:** hace falta un vuelo de prueba local con salida futura a menos de 72 horas y asiento disponible. No cambiar fechas de vuelos existentes para simularla.
3. **Rollback a mitad de escritura:** requiere un clon/dataset local aislado y una inyección controlada de fallo después de una escritura; no se alteraron triggers, permisos ni tablas del entorno activo para forzarlo.
4. **Email de aplicación:** el envío SMTP técnico fue aceptado, pero registro/entrega de activación/activación/recuperación y recepción deben repetirse con un buzón de prueba accesible. No se deben usar ni publicar credenciales SMTP.
5. **Lectores de pantalla:** no se encontró proceso NVDA/Narrator activo y no se probó VoiceOver; ejecutar la revisión con una herramienta disponible.
6. **Paginación:** el dataset usado devolvió una coincidencia y no presentó paginador; probar cuando haya suficientes vuelos para generar páginas.
7. **W3C:** habilitar URL pública accesible y ejecutar ambos validadores; **Pendiente de ejecutar sobre URL pública.**
8. **Performance:** ejecutar medición reproducible de carga y trazado SQL en un entorno representativo antes de optimizar imágenes o consultas.
9. Contrastar contra la rúbrica oficial del TP; no se encontró una rúbrica independiente en los archivos revisados.

## 16. Requisitos del TP

| Área | Resultado | Alcance |
|---|---|---|
| Sistema de aerolíneas PHP/MySQL | ✅ Revisado | Esquema y páginas del proyecto incluyen vuelos, aerolíneas, reservas, usuarios y contenido asociado. |
| Reserva transaccional | ✅ Probada en esta ronda | Reserva real temporal con promoción, asiento y disponibilidad; cancelación >72 h restauró los valores. La cuenta y cinco reservas temporales se eliminaron, y la BD volvió a su conteo inicial. |
| Roles | ⚠️ Parcial | CEO cuenta con prueba dinámica histórica y pasajero se probó con cuenta temporal; el login admin sigue pendiente por falta de contraseña conocida. |
| Correo | ⚠️ Parcial | PHPMailer envió un mensaje técnico; recepción y flujos de activación/recuperación quedan pendientes con buzón de prueba. |
| Stack requerido | ✅ | No se añadieron tecnologías ni arquitectura fuera de PHP/MySQL/Bootstrap/JS existentes. |
| Conformidad académica final | ⚠️ Pendiente | Falta cotejo con una rúbrica oficial externa al repositorio. |

## 17. Elementos no aplicables

| Elemento | Motivo |
|---|---|
| Framework de frontend adicional | No forma parte de la arquitectura solicitada y no se añadió. |
| Pago real con proveedor externo | No se configuró ni se probó; esta auditoría no presupone que el alcance del TP exija pagos reales. |
| Modal Bootstrap complejo | No se identificó un flujo modal complejo en las páginas inspeccionadas; esto no afirma inexistencia en todas las rutas no probadas. |

## 18. Elementos pendientes o no verificados

- Login admin con cuenta de prueba local y contraseña conocida.
- Login admin con cuenta de prueba local y contraseña conocida.
- Cancelación <72 h; no existe vuelo programado local con salida próxima suficiente para probar ese caso sin crear/modificar un fixture.
- Fallo de SQL después de una escritura parcial dentro de la transacción; solo se ejercitó el rechazo transaccional por etiqueta de asiento inconsistente antes de persistir.
- Recepción y verificación por enlace de emails de registro, activación y recuperación; el envío SMTP técnico sí fue aceptado.
- Lector de pantalla y paginación real (no había paginador en el dataset).
- Validadores W3C: **Pendiente de ejecutar sobre URL pública.**
- Medición de carga, rendimiento y trazado SQL reproducibles.
- Cotejo contra una rúbrica oficial que no se encontró en el proyecto.

## 19. Cambios realizados durante esta revisión

| Área | Cambio |
|---|---|
| Accesibilidad de navegación | Se añadió destino `<main id="contenido-principal" tabindex="-1">` a los 26 templates de `pages/`. |
| Tablas anchas | Se envolvieron tablas de ocho páginas administrativas/CEO en regiones con nombre, foco y scroll horizontal contenido. |
| Mapa de asientos | Los botones comunican `aria-pressed`, cambian su nombre accesible según estado y bloquean/anuncian asientos ocupados; se conserva la activación nativa de botones. |
| Contraste y responsive | Se ajustaron colores de placeholders/estados, la distinción de asientos ocupados y el breakpoint del menú a XL; se añadieron reglas para desplazamiento de tablas. |
| Teclado y autenticación | Se añadió destino del skip link en login/registro/recuperación/restablecimiento y un foco `:focus-visible` explícito al toggler del navbar. |
| Recursos de marca | Se creó el derivado PNG de 256 × 256, se actualizó su uso, se corrigió el favicon y se aplicó cache-busting al CSS. |
| Correo de activación | Se alinearon los colores de la plantilla con la marca VOLARA. |

Para estas pruebas se crearon y luego se eliminaron dos cuentas pasajero desechables y cinco reservas de prueba. Se verificó antes de eliminarlas que todas estaban canceladas; después se confirmó que la cuenta temporal ya no existía, el conteo de reservas volvió a 3 (su línea base), el vuelo conservaba 14/15 asientos disponibles y los dos asientos usados estaban disponibles. No se tocaron cuentas reales ni se hizo cambio permanente de datos.

## 20. Conclusión y matriz objetiva

Las correcciones abordaron defectos observados de navegación por teclado, estados de asientos, contraste y desbordamiento responsive. El recorrido autenticado de reserva pasó en desktop y mobile; la cancelación >72 h y una carrera de dos sesiones también pasaron, con limpieza y restauración verificadas. Siguen pendientes el login admin, la cancelación <72 h, el fallo SQL a mitad de transacción, los flujos de correo desde cuenta hasta recepción, lector de pantalla y validación W3C pública. Por eso, **no corresponde declarar el sistema completamente validado**.

| Prueba | Resultado | Evidencia / límite |
|---|---|---|
| Funcionalidad general | ⚠️ | Reserva completa y cancelación >72 h probadas; cancelación <72 h y fallo SQL a mitad de transacción pendientes. |
| Usabilidad | ✅ | Navegación, contenido y feedback revisados en las rutas probadas. |
| Accesibilidad | ⚠️ | Teclado/foco visible probado y corregido; lector de pantalla y evaluación completa pendientes. |
| Rapidez de acceso | ✅ | Navbar/skip link revisados; no representa una métrica de velocidad. |
| Responsive | ✅ Parcial | El recorrido de pasajero pasa a 1280 × 900 y 390 × 844; login admin no disponible para matriz de ese rol. |
| HTML/CSS/W3C | ⚠️ | Comprobaciones locales focalizadas; **Pendiente de ejecutar sobre URL pública.** |
| Seguridad | ⚠️ | CEO y pasajero probados en los alcances indicados; cuenta admin y cobertura exhaustiva pendientes. |
| Navegación | ✅ | En las rutas probadas, menú, enlaces por rol visible y destino principal funcionan. |
| Formularios | ✅ Parcial | Búsqueda inválida enfocó/anunció error; checkout/cancelación activados por teclado. Registro y recuperación por email pendientes. |
| Errores/404 | ✅ | 404 real confirmado; sin errores JS en los recorridos reservados. |
| Branding | ✅ | Identidad consistente en superficies revisadas; email de prueba SMTP enviado, recepción visual pendiente. |
| Rendimiento | ⚠️ | Pesos/duplicados revisados; no hay medición reproducible ni trazado SQL. |
| Requisitos TP | ⚠️ | Parte crítica probada; quedan las limitaciones indicadas en esta auditoría. |

## Últimas pruebas de cierre

Fecha de ejecución: **2026-10-04** (XAMPP local). Los registros de la cuenta y reservas temporales se limpiaron al concluir.

| Fecha | Prueba | Resultado | Evidencia | Limitación |
|---|---|---|---|---|
| 2026-10-04 | Credenciales/ingreso admin | ⚠️ IMPLEMENTADO PERO PENDIENTE DE PRUEBA MANUAL | El registro admin existente solo conserva un hash; no se intentó adivinar ni cambiar su contraseña. | Hace falta una cuenta admin temporal local con contraseña conocida, `password_hash()`, rol admin, activa y verificada. No modificar cuentas reales. |
| 2026-10-04 | CEO | ✅ PROBADO Y APROBADO | Se conserva la evidencia de inicio de sesión y aislamiento CEO de la validación dinámica previa. | No se repitió el login CEO durante esta ronda. |
| 2026-10-04 | Ingreso pasajero temporal | ✅ PROBADO Y APROBADO | Cuenta desechable autenticó en navegador y se usó para recorrer el flujo; después se eliminó. | Cuenta verificada por fixture de prueba, no por email recibido/activado. |
| 2026-10-04 | Reserva desktop 1280 × 900 | ✅ PROBADO Y APROBADO | Búsqueda AEP→MAD, detalle, selección 1A, confirmación y Mis reservas; todas las páginas del flujo con `scrollWidth == clientWidth`. | Un único vuelo local futuro correspondió a la búsqueda. |
| 2026-10-04 | Reserva mobile 390 × 844 | ✅ PROBADO Y APROBADO | Mismo recorrido y asiento; en búsqueda, resultados, detalle, asiento, confirmación y Mis reservas no hubo overflow ni errores de consola/página. | Chromium reporta área de documento de 375 px por scrollbar; viewport solicitado fue 390 × 844. |
| 2026-10-04 | Precio/promoción/estado | ✅ PROBADO Y APROBADO | Precio original $1.300.000; descuento 9,12% ($118.560); final $1.181.440. La reserva apareció `pendiente_pago`, con asiento 1A y código. | Corresponde a la promoción vigente y vuelo existentes en la BD local en esta fecha. |
| 2026-10-04 | Doble envío/recarga y volver atrás | ✅ PROBADO Y APROBADO | Repetir el POST con CSRF válido fue rechazado porque el asiento ya no estaba disponible; recargar la confirmación mostró rechazo; al volver atrás, el asiento reservado aparecía deshabilitado. | No es una prueba de red inestable ni de reintentos concurrentes masivos. |
| 2026-10-04 | Mis reservas/identidad | ✅ PROBADO Y APROBADO | La cuenta temporal solo mostró sus propias reservas, estados y asientos creados durante la prueba. | No se usó una segunda cuenta para intentar leer una reserva ajena en esta ronda. |
| 2026-10-04 | Cancelación >72 h | ✅ PROBADO Y APROBADO | La salida local era 2026-12-08; la UI confirmó cancelación. BD mostró `cancelada`, asiento disponible y disponibilidad restaurada de 13 a 14/15. | No se tocó el horario existente. |
| 2026-10-04 | Cancelación <72 h | ⚠️ IMPLEMENTADO PERO PENDIENTE DE PRUEBA MANUAL | No había vuelo programado con salida futura dentro de 72 horas; se preservaron las fechas existentes. | Requiere vuelo fixture desechable en BD local aislada, con asiento disponible y salida <72 h. |
| 2026-10-04 | Concurrencia por mismo asiento | ✅ PROBADO Y APROBADO | Dos sesiones HTTP independientes enviaron el mismo vuelo/asiento simultáneamente: exactamente una reserva creada y la otra rechazada; la ganadora se canceló y limpió. | Prueba local acotada a una carrera de dos sesiones, no prueba de carga. |
| 2026-10-04 | Rechazo/rollback de selección inconsistente | ✅ PROBADO Y APROBADO | Checkout rechazó una etiqueta que no coincidía con el ID del asiento; no creó reserva y la BD quedó sin cambios. | La validación falla antes de las escrituras; no se inyectó una excepción SQL después de insertar dentro de la transacción. |
| 2026-10-04 | Rollback ante fallo a mitad de transacción | ⚠️ IMPLEMENTADO PERO PENDIENTE DE PRUEBA MANUAL | Código usa transacción y rollback; se verificó el rechazo anterior sin efectos parciales. | Requiere clon local y fallo inyectado después de una escritura para probar atomicidad de ese caso. |
| 2026-10-04 | SMTP PHPMailer | ✅ PROBADO Y APROBADO | `sendVolaraEmail()` devolvió envío aceptado al buzón configurado como remitente; no se imprimieron valores de configuración. | No se leyó la bandeja receptora ni se probó entrega de activación/recuperación. |
| 2026-10-04 | Registro, activación y recuperación por email | ⚠️ IMPLEMENTADO PERO PENDIENTE DE PRUEBA MANUAL | Se revisaron rutas y plantillas; SMTP técnico funciona. | Necesita buzón de prueba accesible y comprobar recepción, enlace de activación y enlace de reset. |
| 2026-10-04 | Teclado/foco visible | ✅ PROBADO Y APROBADO | Tab llegó a skip link, marca y toggler; Enter abrió menú; formularios enfocaron el error; Enter/Space seleccionaron/deseleccionaron asiento y Enter envió checkout/cancelación. Se corrigió foco del toggler y el destino skip link de cuatro páginas auth; `php -l` pasó en los cuatro PHP. | No incluye todas las páginas admin ni dispositivos/navegadores adicionales. |
| 2026-10-04 | Login/registro/recuperación: skip link | ✅ PROBADO Y APROBADO | Las cuatro rutas respondieron HTTP 200; cada una tiene `href="#contenido-principal"` y `<main id="contenido-principal" tabindex="-1">`. | Verifica destino/foco del landmark, no el flujo de recuperación por email. |
| 2026-10-04 | Foco de menú mobile y overflow | ✅ PROBADO Y APROBADO | Toggler recibe outline visible, Enter lo expande; al abrir el menú a viewport 390 × 844, `scrollWidth == clientWidth` y no hay elementos fuera del viewport. | Prueba de navegador local/Chromium. |
| 2026-10-04 | Lector de pantalla | ⚠️ IMPLEMENTADO PERO PENDIENTE DE PRUEBA MANUAL | No había proceso NVDA/Narrator activo en el entorno. | No se probó VoiceOver ni una lectura real con tecnología de asistencia. |
| 2026-10-04 | Paginación por teclado | ⚠️ IMPLEMENTADO PERO PENDIENTE DE PRUEBA MANUAL | El resultado tuvo una coincidencia y no presentó paginador. | Probar cuando el conjunto de vuelos genere más de una página. |
| 2026-10-04 | Preparación local W3C | ✅ PROBADO Y APROBADO (solo preparación) | CSS local cargó y el navegador expuso 214 reglas; se corrigieron fallos evidentes del skip link/foco detectados. | HTML/CSS W3C: **Pendiente de ejecutar sobre URL pública.** No se declara aprobado. |
| 2026-10-04 | Revisión básica de performance | ⚠️ IMPLEMENTADO PERO PENDIENTE DE PRUEBA MANUAL | CSS ≈33 KB, JS ≈9,5 KB; 0 grupos de assets idénticos por SHA-256; imagen mayor ≈1.293,5 KB. | Sin medición reproducible de carga ni trazado SQL; no se optimizó sin métrica objetivo. |
| 2026-10-04 | Limpieza de datos temporales | ✅ PROBADO Y APROBADO | Dos cuentas pasajero temporales y cinco reservas fueron eliminadas tras verificar que estaban canceladas. La BD volvió a 3 reservas, vuelo 6 a 14/15 disponibles y los dos asientos de prueba disponibles. | La comprobación solo certifica los registros temporales de este cierre y el vuelo usado. |
| 2026-10-04 | Validación final de PHP y diff | ✅ PROBADO Y APROBADO | `php -l` pasó en los 47 PHP de aplicación fuera de `vendor/`; `git diff --check` pasó. | Sintaxis/whitespace no sustituyen pruebas funcionales externas. |

### IMPLEMENTADO Y VERIFICADO

- Flujo autenticado de pasajero probado en navegador en 1280 × 900 y 390 × 844; precio, descuento, asiento, reserva, doble envío/recarga, regreso y Mis reservas comprobados.
- Cancelaciones de prueba de vuelos a más de 72 horas y carrera controlada de dos sesiones sobre el mismo asiento.
- Foco visible, menú, validación de búsqueda y selección/envío/cancelación mediante teclado.
- Envío de mensaje técnico mediante PHPMailer/SMTP aceptado por el servidor y limpieza de los datos de fixture.

### IMPLEMENTADO PERO PENDIENTE DE PRUEBA

- Login y operaciones con cuenta admin: no hay una contraseña de prueba conocida; no cambiar cuentas reales.
- Cancelación a menos de 72 horas: requiere crear un vuelo de prueba local aislado en estado `programado`, con salida futura dentro de 72 horas y al menos un asiento `disponible`; conservar capturas de disponibilidad antes/después. No alterar vuelos existentes.
- Fallo SQL después de una escritura parcial: preparar clon local/fixture aislado e inyectar fallo después de la primera escritura de la transacción; confirmar rollback de reserva, asiento y disponibilidad.
- Recepción de correo y flujos de registro/verificación/recuperación en un buzón de prueba accesible. El envío técnico SMTP aceptado no prueba entrega en bandeja ni validez de enlaces.
- Lector de pantalla, paginación con más de una página y medición reproducible de rendimiento/trazado SQL.

### PENDIENTE DE URL PÚBLICA

- Validación formal W3C HTML y W3C CSS: **Pendiente de ejecutar sobre URL pública.** No se declara aprobada la validación externa.

### PRUEBAS QUE DEBO REALIZAR MANUALMENTE

1. Crear una cuenta admin temporal en BD local aislada con email controlado, contraseña nueva conocida almacenada con `password_hash()`, rol `admin`, activa y verificada; probar y luego borrar solo esa cuenta desechable.
2. Crear en BD local aislada un vuelo `programado` con salida dentro de 72 horas, disponibilidad coherente y al menos un asiento disponible; probar el rechazo de cancelación y confirmar que reserva, asiento y conteo no cambian.
3. En una copia local controlada, provocar un error después de una escritura parcial de la transacción de reserva y comprobar que no queda ningún cambio parcial.
4. Usar un buzón de prueba que se pueda consultar; enviar registro, activar con el enlace recibido, solicitar recuperación y seguir el enlace de reset. No compartir ni pegar credenciales SMTP en el repositorio.
5. Activar un lector de pantalla y probar navegación, headings, formularios, asiento y mensajes; repetir la paginación cuando existan resultados suficientes.
6. Publicar temporalmente una URL segura para ejecutar los validadores W3C HTML/CSS y registrar sus resultados.
7. Ejecutar un perfil de carga reproducible y trazado de consultas antes de decidir si se optimiza la imagen de destino o las consultas.
