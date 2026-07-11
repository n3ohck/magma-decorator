# Manual de operación y configuración — Decorador Virtual Magma

> Documento para administradores y usuarios operativos. No se necesitan conocimientos técnicos.

**Versión revisada:** julio de 2026

---

# 1. ¿Para qué sirve este sistema?

El Decorador Virtual Magma permite mostrar distintos ambientes —por ejemplo, cocinas, baños o exteriores— y aplicar materiales sobre zonas específicas de cada imagen, como pisos, muros, barras o fachadas.

El sistema tiene dos partes:

1. **Decorador público:** es la pantalla que utiliza el visitante para elegir un ambiente, seleccionar una zona, aplicar materiales, ajustar el resultado y, cuando está habilitado, generar un render con inteligencia artificial.
2. **Panel administrativo o “Decorator Builder”:** es la pantalla privada donde el personal autorizado crea y administra categorías, materiales, ambientes, zonas y grupos de zonas.

El orden recomendado para cargar información nueva es:

1. Crear las categorías de materiales.
2. Crear los materiales.
3. Crear el ambiente.
4. Crear las zonas editables del ambiente.
5. Crear grupos si varias zonas deben compartir el mismo material.
6. Revisar el resultado en el decorador público.

> 📷 **FOTO 1 — Portada del manual:** colocar aquí una captura del decorador público mostrando un ambiente con uno o más materiales aplicados. La captura debe mostrar la imagen completa y el panel lateral.

---

# 2. Acceso al panel administrativo

1. Abra la dirección de acceso administrativo proporcionada por Magma.
2. Inicie sesión con su cuenta autorizada.
3. Al entrar verá el **Dashboard** del “Decorador Builder”.

En computadoras, el menú aparece del lado izquierdo. En pantallas pequeñas, aparece como una barra horizontal en la parte superior.

Las secciones principales son:

- **Dashboard:** resumen y preferencias generales.
- **Categorías:** familias que organizan los materiales.
- **Materiales:** texturas y datos de cada producto.
- **Ambientes:** escenas que verá el visitante.
- **Zonas:** partes editables de cada ambiente.
- **Grupos:** unión de varias zonas para que compartan material.

Para salir, presione **Cerrar sesión**. Siempre cierre la sesión si usa una computadora compartida.

> 📷 **FOTO 2 — Acceso:** colocar aquí una captura de la pantalla de inicio de sesión, sin mostrar una contraseña real.

> 📷 **FOTO 3 — Menú principal:** colocar aquí una captura del Dashboard completo, señalando el menú izquierdo y el botón “Cerrar sesión”.

---

# 3. Reglas generales para trabajar sin errores

- Los campos marcados como **obligatorios** deben llenarse para poder guardar.
- Los campos **opcionales** pueden dejarse vacíos.
- Al crear un registro, el **slug** se genera automáticamente a partir del nombre. Normalmente no debe modificarse.
- Use nombres claros y consistentes. Ejemplo: “Mármol”, “Calacatta Gold”, “Cocina contemporánea” y “Piso”.
- Utilice el campo **Orden** para decidir qué aparece primero. Un número menor aparece antes: 0 antes que 10, y 10 antes que 20.
- Un registro **Activo** aparece disponible en el decorador. Un registro inactivo permanece guardado, pero no se muestra al visitante.
- Antes de eliminar, prefiera desactivar el registro y revisar el decorador. Eliminar puede afectar otros registros relacionados.
- Después de guardar, espere el mensaje de confirmación antes de cerrar o cambiar de sección.
- Revise siempre el resultado en una computadora y, si es posible, también en un teléfono.

## ¿Qué es un slug?

Es el nombre interno que se usa en direcciones web. No lleva acentos, espacios ni símbolos.

Ejemplo:

- Nombre: `Cocina Contemporánea`
- Slug: `cocina-contemporanea`

El slug debe ser único cuando el sistema así lo solicita. Si aparece un error de duplicado, agregue una palabra o número, por ejemplo: `cocina-contemporanea-2`.

## ¿Qué son Keywords / Tags?

Son palabras que ayudan a describir y localizar un registro. Escriba una palabra o frase corta y presione **Enter** o el botón **+** para agregarla.

Ejemplos: `mármol`, `blanco`, `cocina`, `exterior`, `acabado mate`.

No repita el nombre completo muchas veces. Use entre 3 y 8 etiquetas útiles.

---

# 4. Preparación de imágenes para web

El sistema acepta **PNG, JPG/JPEG y WebP** y optimiza las imágenes automáticamente. El límite técnico de carga es **50 MB por archivo**, pero no es recomendable acercarse a ese tamaño.

## Peso recomendado antes de subir

- **Imagen base del ambiente:** idealmente entre 500 KB y 2 MB; máximo recomendado 3 MB.
- **Preview del ambiente:** idealmente entre 100 KB y 500 KB.
- **Textura del material:** idealmente entre 200 KB y 1 MB.
- **Miniatura del material:** idealmente entre 50 KB y 250 KB.
- **Overlay de sombras, luces o primer plano:** idealmente entre 200 KB y 1.5 MB.
- **Máscara:** idealmente entre 50 KB y 500 KB, dependiendo del tamaño y la complejidad.

Un archivo ligero carga más rápido y mejora la experiencia, especialmente en teléfonos y conexiones móviles.

## Tamaños recomendados

- **Imagen base:** 1600 × 1000 px o 1920 × 1200 px. El sistema admite hasta 2400 × 2400 px sin reducir.
- **Preview:** 1200 px o menos en su lado mayor.
- **Textura:** hasta 1400 × 1400 px. Preferentemente cuadrada y sin uniones visibles.
- **Miniatura:** 600 × 600 px. Preferentemente cuadrada.
- **Overlays:** exactamente las mismas dimensiones y proporción que la imagen base.
- **Máscaras:** exactamente las mismas dimensiones que la imagen base y el canvas del ambiente.

## Formato recomendado

- Use **JPG** para fotografías sin transparencia.
- Use **PNG** cuando necesite transparencia, como en máscaras y overlays.
- Use **WebP** si ya cuenta con un archivo optimizado y compatible con transparencia.

El sistema guarda normalmente una copia optimizada en WebP. Nunca aumenta una imagen pequeña; sólo reduce las imágenes que exceden los límites internos.

## Recomendaciones de calidad

- No suba capturas de pantalla como imagen base.
- Evite imágenes borrosas, oscuras o con marcas de agua.
- No estire una imagen pequeña para hacerla grande.
- Mantenga la misma proporción entre base, overlays, canvas y máscaras.
- En texturas, evite bordes, sombras externas, texto, logotipos y fotografías en perspectiva.
- Antes de subir, use nombres entendibles, por ejemplo: `cocina-moderna-base.jpg` o `calacatta-gold-textura.webp`.

> 📷 **FOTO 4 — Comparación de archivos:** colocar aquí una imagen comparativa con un ejemplo correcto y uno incorrecto: imagen nítida y ligera frente a imagen borrosa o excesivamente pesada.

---

# 5. Dashboard y configuración general

El Dashboard muestra un resumen de:

- Total de categorías.
- Materiales activos.
- Ambientes activos.

## Activar o desactivar “Render con IA”

En **Preferencias del decorador** encontrará el interruptor **Botón “Render con IA”**.

- **Activado:** el visitante ve el botón para generar una imagen con inteligencia artificial.
- **Desactivado:** el botón desaparece del decorador público. Los materiales y ambientes siguen funcionando normalmente.

Para cambiarlo, haga clic una sola vez en el interruptor y espere el mensaje de confirmación.

> 📷 **FOTO 5 — Preferencia de IA:** colocar aquí una captura del Dashboard enfocada en el interruptor “Botón Render con IA”, preferentemente mostrando el estado activado.

---

# 6. Categorías de materiales

Una categoría organiza materiales similares. Ejemplos: “Mármoles”, “Granitos”, “Cuarzos” o “Porcelánicos”. Debe existir una categoría activa antes de crear un material.

## Crear una categoría

1. Abra **Categorías**.
2. Presione **+ Nueva categoría**.
3. Complete los campos.
4. Presione **Guardar**.

## Campos de una categoría

- **Nombre — obligatorio:** nombre visible de la categoría. Máximo 255 caracteres.
- **Slug — opcional:** identificador para web. Se genera automáticamente. Debe ser único.
- **Descripción — opcional:** explicación breve de lo que incluye la categoría.
- **Keywords / Tags — opcional:** palabras relacionadas; cada etiqueta admite hasta 100 caracteres.
- **Imagen de portada — opcional:** imagen representativa de la categoría. Acepta PNG, JPG o WebP.
- **Activa — opcional:** marcada por defecto. Si se desmarca, la categoría y sus materiales dejan de mostrarse al visitante.
- **Orden — opcional:** número que determina su posición. Use 0, 10, 20, 30 para poder insertar nuevas categorías después.

## Editar una categoría

1. Localice la categoría en la lista.
2. Presione **Editar**.
3. Cambie únicamente lo necesario.
4. Para reemplazar la portada, quite la actual con **×** y seleccione otra.
5. Presione **Guardar**.

## Desactivar o eliminar una categoría

Para ocultarla temporalmente, edítela y desmarque **Activa**.

Para eliminarla, presione **Eliminar** y confirme. **Precaución:** los materiales que pertenecen a esa categoría también pueden eliminarse debido a la relación entre registros. Por seguridad, primero desactive la categoría o mueva sus materiales a otra categoría.

> 📷 **FOTO 6 — Lista de categorías:** colocar aquí una captura con varias categorías y los botones Editar y Eliminar visibles.

> 📷 **FOTO 7 — Formulario de categoría:** colocar aquí una captura del panel “Nueva categoría” con todos los campos visibles.

---

# 7. Materiales

Un material es el producto o acabado que el visitante aplica sobre una zona. Ejemplos: “Calacatta Gold”, “Negro San Gabriel” o “Travertino Beige”.

## Antes de crear un material

- Cree primero su categoría.
- Prepare una textura limpia y, si es posible, una miniatura cuadrada.
- Verifique el SKU y el nombre comercial.

## Crear un material

1. Abra **Materiales**.
2. Presione **+ Nuevo material**.
3. Complete los campos obligatorios y los opcionales que correspondan.
4. Presione **Guardar material**.
5. Revise la tarjeta creada y pruébela en un ambiente.

## Campos de un material

- **Categoría — obligatorio:** familia a la que pertenece. Sólo aparecen categorías activas.
- **Nombre — obligatorio:** nombre comercial visible. Máximo 255 caracteres.
- **Slug — opcional:** identificador web; se genera automáticamente y debe ser único.
- **SKU — opcional:** código interno o comercial del producto.
- **País de origen — opcional:** país donde se fabrica o extrae.
- **Acabado — opcional:** apariencia de la superficie, por ejemplo pulido, mate, leather o satinado.
- **Color base — opcional:** color predominante, por ejemplo blanco, gris o negro.
- **Orden — opcional:** posición del material en los listados. No admite números negativos.
- **Descripción corta — opcional:** resumen breve para identificar el producto.
- **Keywords / Tags — opcional:** palabras de búsqueda o clasificación; cada una admite hasta 100 caracteres.
- **Textura — obligatoria al crear:** imagen que se repetirá sobre el piso, muro u otra zona. Al editar, puede conservar la existente.
- **Miniatura — opcional:** imagen pequeña que verá el visitante en el selector. Si no se carga, el sistema usa la textura como miniatura.
- **Escala — opcional:** tamaño inicial del patrón. El valor normal es `1`. Debe ser 0.1 o mayor. Un valor mayor hace que el patrón se vea más grande.
- **Opacidad — opcional:** transparencia inicial. Use valores de `0` a `1`: 0 es invisible, 0.5 es semitransparente y 1 es totalmente visible.
- **Destacado — opcional:** marca el material como importante para usos actuales o futuros del sistema.
- **Activo — opcional:** si está marcado, el material puede aparecer al visitante.

El sistema también conserva una **rotación inicial**, con valor normal 0°, aunque actualmente no aparece como campo editable en este formulario.

## Editar un material

1. Abra **Materiales**.
2. Localice la tarjeta y presione **Editar**.
3. Modifique los datos.
4. Para reemplazar una imagen, presione **×** sobre la imagen actual y seleccione la nueva.
5. Presione **Guardar material**.

No es necesario volver a cargar la textura al editar si desea conservarla.

## Desactivar o eliminar un material

- Para ocultarlo sin perderlo, edítelo y desmarque **Activo**.
- Para borrarlo definitivamente, presione **Eliminar** y confirme.

Al eliminar un material, deja de estar disponible en los ambientes. Las sesiones históricas que lo usaban pueden conservar el diseño, pero ya no tendrán el material relacionado. Se recomienda desactivarlo primero.

> 📷 **FOTO 8 — Tarjetas de materiales:** colocar aquí una captura de la sección Materiales con una tarjeta activa y sus botones.

> 📷 **FOTO 9 — Formulario de material:** colocar aquí una captura del formulario completo, incluyendo Textura, Miniatura, Escala, Opacidad, Destacado y Activo.

> 📷 **FOTO 10 — Textura correcta:** colocar aquí un ejemplo de textura cuadrada, frontal, nítida y sin texto, junto con su miniatura.

---

# 8. Ambientes

Un ambiente es la escena principal que el visitante personaliza. Puede ser una cocina, baño, sala, oficina, exterior u otro espacio.

## Crear un ambiente

1. Prepare la imagen base y defina sus dimensiones.
2. Abra **Ambientes**.
3. Presione **+ Nuevo ambiente**.
4. Complete el formulario.
5. Presione **Guardar ambiente**.
6. Después cree sus zonas editables.

## Campos de un ambiente

- **Nombre — obligatorio:** nombre que verá el visitante. Máximo 255 caracteres.
- **Slug — opcional:** identificador web. Se genera automáticamente y debe ser único.
- **Tipo de ambiente — opcional:** Cocina, Baño, Sala, Comercial, Exterior, Oficina u Otro.
- **Orden — opcional:** posición del ambiente. Debe ser 0 o mayor.
- **Ancho canvas — opcional:** ancho de trabajo en píxeles. Mínimo 100. Valor inicial: 1600.
- **Alto canvas — opcional:** alto de trabajo en píxeles. Mínimo 100. Valor inicial: 1000.
- **Descripción — opcional:** descripción breve del espacio.
- **Keywords / Tags — opcional:** palabras relacionadas; cada una admite hasta 100 caracteres.
- **Imagen base del ambiente — obligatoria al crear:** fotografía principal sobre la que se aplican los materiales.
- **Imagen preview — opcional:** imagen usada en la tarjeta del listado público. Si no se carga, el sistema puede utilizar la base.
- **Overlay de sombras — opcional:** capa transparente que recupera sombras realistas por encima de los materiales.
- **Overlay de luces — opcional:** capa transparente para conservar reflejos o iluminación.
- **Overlay frontal / objetos encima — opcional:** capa transparente con objetos que deben quedar por delante del material, como muebles, plantas, grifería o decoración.
- **Ambiente destacado — opcional:** lo señala como ambiente importante.
- **Activo — opcional:** permite que aparezca en el decorador público.
- **Materiales disponibles — opcional:** define qué materiales podrá utilizar el visitante en este ambiente.

## Configurar materiales disponibles

- Marque **Todos** para permitir todos los materiales activos de todas las categorías activas.
- Desmarque **Todos** para elegir manualmente categorías o materiales específicos.
- Use **Seleccionar todos** o **Ninguno** para acelerar la selección.
- Si una categoría o material está inactivo, no aparecerá en esta selección ni en el decorador público.

## Relación entre imagen base, canvas, overlays y máscaras

Todos deben usar la misma proporción y, de preferencia, las mismas dimensiones.

Ejemplo correcto:

- Base: 1600 × 1000 px.
- Canvas: 1600 × 1000.
- Overlay de sombras: 1600 × 1000 px.
- Overlay de luces: 1600 × 1000 px.
- Overlay frontal: 1600 × 1000 px.
- Cada máscara: 1600 × 1000 px.

Si no coinciden, la textura puede aparecer desplazada, estirada o fuera de la zona.

## Editar un ambiente

1. Localice el ambiente y presione **Editar**.
2. Modifique los campos necesarios.
3. Para reemplazar una imagen, quite la actual con **×** y cargue otra.
4. Si cambia las dimensiones o el encuadre de la imagen base, vuelva a crear o verificar todas sus máscaras y overlays.
5. Presione **Guardar ambiente**.

## Desactivar o eliminar un ambiente

Para ocultarlo temporalmente, desmarque **Activo**.

Al presionar **Eliminar**, el sistema avisa que también se eliminarán sus zonas. Además, se eliminan sus grupos y relaciones con materiales. Esta acción puede dejar sesiones históricas sin ambiente. Haga una copia de las imágenes y confirme que ya no se necesita antes de eliminar.

> 📷 **FOTO 11 — Lista de ambientes:** colocar aquí una captura con una tarjeta de ambiente, su estado y la cantidad de zonas.

> 📷 **FOTO 12 — Formulario de ambiente, parte 1:** colocar aquí una captura desde Nombre hasta las imágenes.

> 📷 **FOTO 13 — Formulario de ambiente, parte 2:** colocar aquí una captura de “Materiales disponibles” mostrando la opción Todos y la selección manual.

> 📷 **FOTO 14 — Capas del ambiente:** colocar aquí un ejemplo visual separado de Base, Sombras, Luces y Overlay frontal, todos con el mismo tamaño.

---

# 9. Zonas de ambientes

Una zona es una parte editable de un ambiente. Ejemplos: piso, muro, cubierta, backsplash, isla, fachada o muro de ducha.

Cada zona necesita una **máscara**, que indica exactamente dónde debe aparecer el material.

## Crear una zona

1. Abra **Zonas**.
2. Presione **+ Nueva zona**.
3. Seleccione el ambiente.
4. Escriba el nombre y el tipo de zona.
5. Cree la máscara con el editor de polígono o cargue una máscara preparada.
6. Ajuste los valores visuales si es necesario.
7. Presione **Guardar zona**.

## Campos de una zona

- **Ambiente — obligatorio:** escena a la que pertenece la zona. Sólo aparecen ambientes activos.
- **Nombre de zona — obligatorio:** nombre visible, por ejemplo Piso, Muro izquierdo o Barra. Máximo 255 caracteres.
- **Slug — opcional:** identificador interno. Se genera automáticamente. Dentro de un mismo ambiente no deben repetirse slugs.
- **Grupo de zonas — opcional:** grupo con el que compartirá material. Sólo aparecen grupos del ambiente seleccionado.
- **Tipo de zona — opcional:** Piso, Muro, Cubierta/Barra, Backsplash, Isla, Fachada, Muro de ducha u Otro.
- **Orden — opcional:** posición de la zona; debe ser 0 o mayor.
- **Escala textura — opcional:** tamaño inicial del patrón; valor normal 1 y mínimo 0.1.
- **Rotación textura — opcional:** giro inicial en grados; valor normal 0.
- **Opacidad — opcional:** transparencia entre 0 y 1; valor normal 1.
- **Soporta perspectiva avanzada — opcional:** indica que la zona puede utilizar corrección de perspectiva. Déjelo desactivado salvo que el equipo visual haya preparado la zona para ello.
- **Book Match por defecto — opcional:** inicia la textura con un espejo simétrico. Úselo para vetas que deban formar un patrón reflejado.
- **Máscara — obligatoria al crear:** puede producirse con el editor o cargarse como imagen. Al editar, puede conservar la existente.
- **Activa — opcional:** permite que la zona funcione en el decorador público.

## Crear una máscara con el editor de polígono

1. Seleccione primero el ambiente para ver su imagen base.
2. En **Editor de máscara**, elija **Polígono**.
3. Haga clic alrededor del borde de la zona. Coloque varios puntos para seguir la forma con precisión.
4. Si se equivoca, presione **Deshacer**.
5. Al tener por lo menos 3 puntos, presione **Cerrar forma**.
6. Revise el área marcada.
7. Presione **Usar esta máscara**.
8. Espere el mensaje **Máscara guardada** y revise la verificación sobre el ambiente.
9. Finalmente presione **Guardar zona**. Guardar la máscara no reemplaza el último paso de guardar la zona.

Use **Limpiar** para empezar de nuevo.

Aunque el panel menciona “SAM 2”, la opción automática por inteligencia artificial está actualmente oculta. La herramienta disponible para operación normal es el polígono.

## Cargar una máscara preparada

En **Máscara PNG**, presione **Seleccionar imagen** y elija el archivo.

La máscara debe:

- Tener exactamente el mismo tamaño que la base y el canvas.
- Mostrar la zona editable y dejar transparente el resto.
- Tener bordes limpios, sin manchas ni huecos involuntarios.
- Conservar transparencia.

Ejemplo: si el ambiente es 1600 × 1000, la máscara debe ser 1600 × 1000.

## Editar una zona

1. Presione **Editar**.
2. Cambie los datos necesarios.
3. Para reemplazar la máscara, quite la anterior con **×**, cree una nueva o seleccione otro archivo.
4. Presione **Guardar zona**.

## Desactivar o eliminar una zona

- Desmarque **Activa** para ocultarla sin borrarla.
- Presione **Eliminar** y confirme para borrarla definitivamente.

Al eliminar una zona se elimina también su máscara y pueden desaparecer las selecciones históricas relacionadas con esa zona. Prefiera desactivarla cuando exista duda.

> 📷 **FOTO 15 — Lista de zonas:** colocar aquí una captura con columnas Ambiente, Zona, Grupo, Tipo, Máscara y Activa.

> 📷 **FOTO 16 — Formulario de zona:** colocar aquí una captura con los campos de ambiente, nombre, grupo, tipo y ajustes visuales.

> 📷 **FOTO 17 — Editor de máscara:** colocar aquí una secuencia de tres capturas: puntos colocados, forma cerrada y verificación de la máscara.

> 📷 **FOTO 18 — Máscara correcta:** colocar aquí la imagen base junto a su máscara, indicando que ambas tienen las mismas dimensiones.

---

# 10. Grupos de zonas

Un grupo une varias zonas del mismo ambiente para que compartan un material. Por ejemplo, dos muros separados pueden pertenecer al grupo “Muros”; cuando el visitante aplica un material, se aplica a ambos.

## Cuándo usar un grupo

- Cuando varias partes deben cambiar juntas.
- Cuando se necesita un solo punto “+” para seleccionar varias zonas.
- Cuando el Book Match debe aplicarse al conjunto.

No agrupe zonas que el visitante deba personalizar por separado.

## Crear un grupo

1. Cree primero el ambiente y sus zonas.
2. Abra **Grupos**.
3. Presione **+ Nuevo grupo**.
4. Seleccione el ambiente.
5. Escriba el nombre.
6. Marque las zonas que formarán parte del grupo.
7. Coloque el punto sobre la imagen.
8. Presione **Guardar grupo**.

## Campos de un grupo

- **Ambiente — obligatorio:** ambiente al que pertenece. Sólo aparecen ambientes activos.
- **Nombre del grupo — obligatorio:** nombre visible, por ejemplo Muros, Pisos o Cubiertas. Máximo 255 caracteres.
- **Slug — opcional:** identificador interno; se genera automáticamente.
- **Color identificador — opcional:** color del punto y etiqueta que verá el visitante. Puede elegirse con el selector o escribirse como código, por ejemplo `#CC1A1A`.
- **Orden — opcional:** posición del grupo; debe ser 0 o mayor.
- **Zonas del grupo — opcional:** zonas activas del ambiente que compartirán material.
- **Posición del punto en la imagen — opcional:** lugar donde aparecerá el botón “+”. Haga clic sobre la imagen para colocarlo.
- **Activo — opcional:** permite mostrar y utilizar el grupo.
- **Book Match por defecto — opcional:** inicia el grupo con espejo simétrico.

El sistema guarda internamente la posición como porcentaje horizontal y vertical. El usuario operativo sólo necesita hacer clic en el lugar correcto.

## Editar un grupo

1. Presione **Editar**.
2. Modifique nombre, color, zonas o posición.
3. Use **Quitar punto** si no desea mostrar el marcador.
4. Presione **Guardar grupo**.

Al guardar una edición, las zonas que se desmarquen quedan sin ese grupo. Una zona sólo puede pertenecer a un grupo a la vez.

## Eliminar un grupo

Presione **Eliminar** y confirme. Las zonas no se eliminan: quedan guardadas, pero sin grupo. Después revise si deben asignarse a otro grupo.

> 📷 **FOTO 19 — Lista de grupos:** colocar aquí una captura mostrando color, nombre, ambiente, zonas, punto y estado.

> 📷 **FOTO 20 — Formulario de grupo:** colocar aquí una captura con selección de zonas y el punto “+” colocado sobre el ambiente.

---

# 11. Solicitudes o prospectos (Leads)

Una solicitud se genera cuando un visitante envía sus datos desde el decorador. El sistema guarda los datos del cliente y una copia de la selección del diseño.

> Nota operativa: actualmente el botón público **Solicitar cotización** está oculto en la interfaz. La función y la sección de solicitudes existen, pero sólo recibirán nuevos registros si ese botón vuelve a habilitarse o si otra integración envía solicitudes.

La sección se encuentra en **Leads / Solicitudes**. Si no aparece en el menú lateral, puede abrirse directamente en `/admin/builder/leads` dentro del sitio administrativo.

## Datos que envía el visitante

- **Nombre — obligatorio:** máximo 150 caracteres.
- **Teléfono — opcional:** máximo 50 caracteres.
- **Ciudad — opcional:** máximo 100 caracteres.
- **Correo — opcional:** debe tener formato válido y máximo 150 caracteres.
- **Tipo de proyecto — opcional:** Cocina, Baño, Residencial, Comercial o Exterior.
- **Mensaje — opcional:** máximo 2,000 caracteres.
- **Método de contacto preferido — automático:** actualmente se guarda WhatsApp por defecto.
- **Ambiente y selección de materiales — automáticos:** el sistema conserva el contexto del diseño.

Los prospectos no se crean manualmente desde el panel administrativo; llegan desde el decorador.

## Consultar y filtrar solicitudes

Use los filtros:

- **Todos:** todas las solicitudes.
- **Nuevos:** recién recibidos.
- **Contactados:** ya se realizó el primer contacto.
- **Cotizados:** ya recibieron una propuesta.
- **Ganados:** se convirtieron en venta o proyecto.
- **Perdidos:** no continuaron.

Haga clic en una fila para ver teléfono, correo, ciudad, tipo de proyecto, contacto preferido y mensaje.

## Editar una solicitud

1. Presione **Cambiar estatus**.
2. Seleccione el nuevo estatus —este campo es obligatorio—.
3. Use **Mensaje / nota** para guardar o actualizar una nota operativa. Este campo es opcional y sustituye el mensaje almacenado actualmente; no funciona como historial de notas.
4. Presione **Guardar cambios**.

## Eliminar una solicitud

Presione **Eliminar** y confirme. La eliminación es definitiva para el registro del prospecto. La sesión de diseño relacionada puede permanecer guardada. Elimine sólo duplicados, pruebas o registros que la política de datos permita borrar.

> 📷 **FOTO 21 — Lista de solicitudes:** colocar aquí una captura con filtros de estatus y una fila seleccionada. Oculte o difumine datos personales reales.

> 📷 **FOTO 22 — Detalle y cambio de estatus:** colocar aquí una captura del detalle y otra del formulario “Cambiar estatus”, usando datos de prueba.

---

# 12. Uso del decorador público

## Elegir un ambiente

1. Abra el decorador.
2. Revise las tarjetas de ambientes activos.
3. Seleccione el ambiente deseado.

Los ambientes inactivos no se muestran. El orden depende del número configurado en el panel.

## Aplicar un material

1. Seleccione una zona mediante el punto “+” sobre la imagen. Si el ambiente no usa grupos, el sistema puede iniciar con la primera zona disponible.
2. En el panel lateral, abra una categoría.
3. Seleccione un material.
4. Espere a que termine de aplicarse antes de elegir otro.

Si la zona pertenece a un grupo, el material se aplica a todas las zonas del grupo.

## Ajustar el resultado

- **Escala:** hace que el patrón se vea más grande o más pequeño.
- **Rotación:** gira la textura de 0° a 360°.
- **Book Match:** crea una composición espejada de las vetas.
- **2 vías · Vertical:** refleja la textura en dos partes verticales.
- **4 vías · Diamante:** crea una composición simétrica de cuatro partes.
- **Restablecer:** vuelve a los valores iniciales.
- **Limpiar:** quita todos los materiales aplicados.

Con Book Match activo, Escala y Rotación quedan deshabilitadas porque el sistema ajusta automáticamente la composición.

## Render con IA

Si el administrador lo habilitó:

1. Aplique los materiales deseados.
2. Presione **Render con IA**.
3. Confirme la generación.
4. Espere sin cerrar la ventana mientras se procesa.

La IA produce una interpretación visual y puede modificar pequeños detalles. Debe considerarse una referencia conceptual, no una representación exacta de medidas, instalación, veta o color final.

> 📷 **FOTO 23 — Selección de ambiente:** colocar aquí la portada pública con varias tarjetas.

> 📷 **FOTO 24 — Aplicación de material:** colocar aquí una captura con el punto “+”, la zona seleccionada y el catálogo de materiales.

> 📷 **FOTO 25 — Ajustes:** colocar aquí una captura de Escala, Rotación, Book Match y sus modos.

> 📷 **FOTO 26 — Render con IA:** colocar aquí el botón y la ventana de generación, sin incluir datos personales.

---

# 13. Cómo revisar una carga antes de publicarla

Use esta lista cada vez que agregue un ambiente:

- [ ] La categoría está activa.
- [ ] El material está activo y tiene textura.
- [ ] La miniatura corresponde a la textura.
- [ ] El ambiente tiene nombre, base y dimensiones correctas.
- [ ] Base, overlays y máscaras tienen la misma proporción.
- [ ] El ambiente tiene por lo menos una zona activa.
- [ ] Cada zona tiene su máscara correcta.
- [ ] Los grupos contienen únicamente zonas del mismo ambiente.
- [ ] El punto “+” no tapa un elemento importante.
- [ ] Los materiales permitidos son los correctos.
- [ ] Escala, rotación, opacidad y Book Match se ven bien.
- [ ] El ambiente fue revisado en computadora y teléfono.
- [ ] Las imágenes cargan con rapidez.
- [ ] No hay datos de prueba visibles.

La práctica más segura es crear todo como inactivo, probarlo y activar primero los materiales, después las categorías y finalmente el ambiente y sus zonas.

---

# 14. Problemas comunes y solución

## “No aparece mi categoría”

- Revise que esté activa.
- Revise que tenga por lo menos un material activo.
- Revise que el ambiente permita ese material.

## “No aparece mi material”

- Revise que el material y su categoría estén activos.
- Revise que el ambiente esté configurado con **Todos** o que ese material esté seleccionado.
- Confirme que el material tenga textura.

## “No aparece mi ambiente”

- Revise que esté activo.
- Confirme que tenga imagen base.
- Actualice la página del decorador.

## “La textura aparece fuera de lugar”

- Compare el tamaño y la proporción de base, canvas y máscara.
- Revise la máscara sobre la imagen base.
- Confirme que no se cambió la imagen base después de crear la máscara.

## “El material se ve demasiado grande, pequeño o transparente”

- Ajuste la Escala y Opacidad del material o de la zona.
- Como punto inicial, use Escala 1 y Opacidad 1.

## “Dos zonas cambian al mismo tiempo y no deberían”

- Abra **Grupos** y quite una de las zonas del grupo.
- O edite la zona y seleccione **Sin grupo**.

## “Dos zonas deberían cambiar juntas y no lo hacen”

- Cree un grupo o edite uno existente.
- Asegúrese de que ambas zonas estén activas y pertenezcan al mismo ambiente.

## “No puedo guardar una imagen”

- Use PNG, JPG o WebP.
- Verifique que el archivo pese menos de 50 MB.
- Renombre el archivo sin símbolos extraños.
- Pruebe con un archivo más ligero.

## “El sistema dice que el slug ya existe”

- Use otro slug, por ejemplo agregando color, ambiente o número.
- No cambie el slug de registros publicados salvo que sea necesario.

## “El botón Render con IA no aparece”

- Revise el interruptor del Dashboard.
- Actualice el decorador después de activarlo.

## “El editor automático SAM no aparece”

La opción SAM está oculta actualmente. Use el editor **Polígono** o cargue una máscara preparada.

---

# 15. Buenas prácticas de operación

- Use una convención de nombres estable.
- Cargue imágenes optimizadas y conserve los originales fuera del sistema.
- Use números de orden separados por 10.
- No cambie slugs de registros activos sin una razón clara.
- Desactive antes de eliminar.
- Pruebe cada cambio en el decorador público.
- Evite editar el mismo registro desde dos computadoras al mismo tiempo.
- No use datos personales reales en pruebas o capturas para manuales.
- Revise periódicamente solicitudes nuevas y actualice su estatus.
- Mantenga una copia de las imágenes base, texturas, overlays y máscaras en una carpeta organizada por ambiente.

Estructura sugerida para respaldos:

```text
Decorador Magma
├── Categorías
├── Materiales
│   └── Nombre del material
│       ├── textura
│       └── miniatura
└── Ambientes
    └── Nombre del ambiente
        ├── base
        ├── preview
        ├── overlays
        └── máscaras
```

---

# 16. Glosario sencillo

- **Activo:** está disponible para el visitante.
- **Ambiente:** fotografía o escena que se personaliza.
- **Book Match:** composición que refleja una veta para crear simetría.
- **Canvas:** área de trabajo y sus dimensiones.
- **Categoría:** grupo que organiza materiales.
- **Destacado:** registro marcado como importante.
- **Lead o prospecto:** persona que envió una solicitud.
- **Máscara:** imagen que señala el área exacta donde se aplica un material.
- **Material:** producto o acabado que se aplica.
- **Miniatura:** imagen pequeña para identificar un material.
- **Opacidad:** nivel de transparencia.
- **Orden:** número que decide qué aparece primero.
- **Overlay:** capa transparente colocada sobre la composición.
- **Preview:** imagen pequeña usada para presentar un ambiente.
- **Slug:** nombre interno utilizado en la dirección web.
- **Textura:** imagen repetible que representa la superficie del material.
- **WebP:** formato de imagen optimizado para páginas web.
- **Zona:** parte editable de un ambiente.
- **Grupo de zonas:** conjunto de zonas que comparten el mismo material.

---

# 17. Registro de control recomendado

Para mantener orden, registre cada publicación en una tabla de Notion con estas columnas:

- Fecha.
- Responsable.
- Tipo de cambio.
- Registro modificado.
- Estado anterior.
- Estado nuevo.
- Resultado revisado.
- Observaciones.

Ejemplo: `10/07/2026 | Ana | Nuevo material | Calacatta Gold | No existía | Activo | Revisado en cocina 01 | Correcto`.

---

# 18. Resumen rápido

1. Cree la categoría.
2. Cree el material con textura.
3. Cree el ambiente con una base de buena calidad.
4. Haga coincidir canvas, overlays y máscaras.
5. Cree las zonas.
6. Agrupe las zonas que deban cambiar juntas.
7. Defina los materiales permitidos.
8. Pruebe todo antes de activar.
9. Desactive antes de eliminar.
10. Mantenga imágenes ligeras y respaldadas.

