-- Script para crear spots REALES de España con imágenes sin derechos
-- Ejecuta esto en: SQL Editor de Supabase
-- Imágenes de Unsplash (licencia abierta)

INSERT INTO spots (title, description, category, tags, lat, lng, status, user_id, image_path) VALUES
(
  '🏖️ Playa de las Catedrales - Ribadeo',
  'Una de las playas más espectaculares de Galicia con formaciones rocosas únicas. Perfecta para fotografía al atardecer y caminatas entre acantilados.',
  'playa',
  '["playa", "galicia", "acantilados", "fotografía", "atardecer"]'::jsonb,
  43.3823,
  -7.0492,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1505142468610-359e7d316be0?w=500'
),
(
  '🏔️ Picos de Europa - Asturias',
  'Macizo montañoso con vistas panorámicas espectaculares. Ideal para senderismo, escalada y contemplación de la naturaleza.',
  'montaña',
  '["montaña", "asturias", "senderismo", "escalada", "vistas"]'::jsonb,
  43.1629,
  -4.9997,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=500'
),
(
  '🏰 Alcázar de Segovia',
  'Castillo medieval del siglo XII situado en una colina sobre la ciudad de Segovia. Arquitectura gótica impresionante con torres y murallas.',
  'historia',
  '["castillo", "segovia", "historia", "arquitectura", "medieval"]'::jsonb,
  40.9522,
  -4.1181,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=500'
),
(
  '🌲 Parque Natural Ordesa y Monte Perdido',
  'Parque Nacional con bosques de pinos, cañones profundos y cascadas. Fauna variada incluyendo rebecos y águilas reales.',
  'naturaleza',
  '["naturaleza", "huesca", "bosque", "cascadas", "senderismo"]'::jsonb,
  42.5893,
  -0.0151,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=500'
),
(
  '🍷 Bodegas de La Rioja - Haro',
  'Región vinícola más famosa de España con bodegas históricas, viñedos y alojamientos. Tours de cata de vinos de clasificación mundial.',
  'gastronomía',
  '["vino", "la-rioja", "gastronomía", "bodegas", "cata"]'::jsonb,
  42.5803,
  -2.8648,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1510812431401-41d2cab2debf?w=500'
),
(
  '⛪ Catedral de Burgos',
  'Catedral gótica del siglo XIII patrimonio de la UNESCO. Arquitectura extraordinaria con vidrieras, retablos y obras maestras del arte medieval.',
  'religión',
  '["iglesia", "burgos", "gótico", "arquitectura", "patrimonio"]'::jsonb,
  42.3440,
  -3.7003,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1518162996427-04b0b9eb2e66?w=500'
),
(
  '🎨 Sagrada Familia - Barcelona',
  'Basílica modernista diseñada por Gaudí. Obra maestra de la arquitectura. Símbolo de Barcelona y uno de los lugares más visitados de España.',
  'arte',
  '["barcelona", "gaudí", "arquitectura", "modernismo", "patrimonio"]'::jsonb,
  41.4036,
  2.1744,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1560169897-fc0cdbdfa4d5?w=500'
),
(
  '💧 Las Médulas - León',
  'Antiguas minas de oro romanas patrimonio de la UNESCO. Paisaje erosionado con formaciones rocosas rojas espectaculares.',
  'historia',
  '["león", "arqueología", "historia-romana", "paisaje", "senderismo"]'::jsonb,
  42.4589,
  -6.6156,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=500'
),
(
  '🏊 Piscinas Naturales Tobogán - Jaén',
  'Piscinas naturales formadas por el río Hornos. Agua cristalina y fresca rodeada de vegetación. Perfectas para refrescarse en verano.',
  'playa',
  '["jaén", "piscina-natural", "agua", "baño", "verano"]'::jsonb,
  38.1234,
  -3.5678,
  'approved',
  (SELECT id FROM auth.users LIMIT 1),
  'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500'
);
