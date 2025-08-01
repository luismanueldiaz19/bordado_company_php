CREATE TABLE tipo_trabajo (
  tipo_trabajo_id serial PRIMARY KEY,
  name_trabajo varchar(100) NOT NULL,
  area_trabajo text DEFAULT 'N/A',
  index_orden integer DEFAULT 0,
  url_imagen text DEFAULT 'N/A',
  created_at date DEFAULT CURRENT_DATE
);


CREATE TABLE trabajo_campos (
  campo_id serial PRIMARY KEY,
  tipo_trabajo_id integer REFERENCES tipo_trabajo(tipo_trabajo_id) ON DELETE CASCADE,
  nombre_campo varchar(100) NOT NULL,
  tipo_dato varchar(30) DEFAULT 'texto'  -- ejemplo: texto, número, fecha, etc.
);


CREATE TABLE public.list_ordenes (
    list_ordenes_id SERIAL PRIMARY KEY,
    id_cliente INTEGER NOT NULL REFERENCES cliente(id_cliente),
    estado_prioritario VARCHAR(50) DEFAULT 'normal',
    estado_general TEXT DEFAULT 'nuevo registro',
    name_logo TEXT DEFAULT 'N/A',
    num_orden INTEGER UNIQUE GENERATED ALWAYS AS IDENTITY,
    ficha VARCHAR DEFAULT 'N/A',
    observaciones TEXT DEFAULT 'N/A',
    fecha_creacion DATE DEFAULT CURRENT_DATE,
    fecha_entrega DATE NOT NULL,
    estado_entrega VARCHAR(50) DEFAULT 'activo',
    usuario_id INTEGER NOT NULL REFERENCES users(id)
);


CREATE TABLE public.orden_items (
    orden_items_id SERIAL PRIMARY KEY,
	list_ordenes_id INTEGER NOT NULL REFERENCES list_ordenes (list_ordenes_id),
	id_producto INTEGER NOT NULL REFERENCES list_producto (id_producto),
	cant numeric (10,2) default 0,
	detalles_productos text not null,
	nota text,
	estado_produccion varchar(100) default 'pendiente',
	fecha_item_creacion date DEFAULT CURRENT_DATE
);

CREATE TABLE planificacion_work (
    planificacion_work_id SERIAL PRIMARY KEY,
    orden_items_id INTEGER NOT NULL REFERENCES orden_items(orden_items_id),
    id_depart INTEGER NOT NULL REFERENCES departments(id) ON DELETE CASCADE,
    estado_planificacion_work VARCHAR(100) DEFAULT 'en espera'
);



CREATE TABLE public.produccion_trabajos (
    produccion_id SERIAL PRIMARY KEY,
	id_depart INTEGER NOT NULL REFERENCES departments (id),
	planificacion_work_id  INTEGER NOT NULL REFERENCES planificacion_work (planificacion_work_id),
    tipo_trabajo_id INTEGER NOT NULL REFERENCES tipo_trabajo(tipo_trabajo_id),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INTEGER REFERENCES users (id),
    datos JSONB NOT NULL,
	num_orden INTEGER NOT NULL REFERENCES list_ordenes(num_orden),
    observaciones TEXT DEFAULT 'N/A',
	date_start character varying DEFAULT 'N/A'::character varying,
    date_end character varying DEFAULT 'N/A'::character varying,
	estado text default 'PENDIENTE',
	creado_en date DEFAULT CURRENT_DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO list_ordenes (
    id_cliente, estado_prioritario, estado_general, name_logo,
    ficha, observaciones, fecha_entrega, usuario_id
) VALUES (
    13, 'urgente', 'nuevo registro', 'logo_empresa_a.png',
    'F-1021', 'Orden de camisas deportivas', '2025-08-05', 2
);

-- Producto 1: Camisa deportiva
INSERT INTO orden_items (
    list_ordenes_id, id_producto, cant, detalles_productos, nota
) VALUES (
    2, 1, 150, 'Talla M, color rojo', 'Urgente para evento'
);

-- Producto 2: Gorras bordadas
INSERT INTO orden_items (
    list_ordenes_id, id_producto, cant, detalles_productos
) VALUES (
   2, 2, 100, 'Color negro, logo blanco'
);


-- Para el producto Camisas → Departamento de Impresión (id = 3)
INSERT INTO planificacion_work (
    orden_items_id, id_depart
) VALUES (
    2, 3
);

-- Para el producto Gorras → Departamento de Bordado (id = 4)
INSERT INTO planificacion_work (
    orden_items_id, id_depart
) VALUES (
    3, 4
);


-- Bordado de Gorras
INSERT INTO produccion_trabajos (
    id_depart, planificacion_work_id, tipo_trabajo_id,
    usuario_id, datos, num_orden, observaciones, date_start, date_end
) VALUES (
    4, 2, 15, -- Ej: tipo_trabajo_id = 15 (bordado)
    1,
    '{"cantidad": 100, "tipo_hilo": "poliéster", "detalles_logo": "bordado blanco"}',
    2,
    'Se usaron hilos especiales',
    '2025-08-02 08:00:00',
    '2025-08-02 13:00:00'
);


SELECT
    lo.num_orden,
    lo.fecha_creacion,
    lo.fecha_entrega,
    c.nombre AS nombre_cliente,
    
    oi.orden_items_id,
    oi.detalles_productos,
    oi.cant AS cantidad_ordenada,
    
    pw.planificacion_work_id,
    d.name_department,
    pw.estado_planificacion_work,
    
    pt.produccion_id,
    tt.name_trabajo  AS tipo_trabajo,
    pt.fecha,
    u.full_name AS nombre_usuario,
    pt.estado,
    pt.observaciones,
    pt.date_start,
    pt.date_end,
    
    -- Extracción de campos específicos del JSONB 'datos'
    pt.datos->>'cantidad' AS cantidad_producida,
    pt.datos->>'colores_full' AS colores,
    pt.datos->>'tipo_hilo' AS tipo_hilo,
    pt.datos->>'observacion_tecnica' AS observacion_tecnica

FROM list_ordenes lo
JOIN cliente c ON lo.id_cliente = c.id_cliente
JOIN orden_items oi ON lo.list_ordenes_id = oi.list_ordenes_id
JOIN planificacion_work pw ON oi.orden_items_id = pw.orden_items_id
JOIN departments d ON pw.id_depart = d.id

LEFT JOIN produccion_trabajos pt ON pt.planificacion_work_id = pw.planificacion_work_id
LEFT JOIN tipo_trabajo tt ON pt.tipo_trabajo_id = tt.tipo_trabajo_id
LEFT JOIN users u ON pt.usuario_id = u.id

ORDER BY lo.num_orden, oi.orden_items_id, pt.fecha;