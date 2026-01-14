<?php

/**
 * Métodos adicionales para la galería de eventos
 * Extiende la funcionalidad de la clase Eventos
 */

class EventosGaleria
{

    public static function getGalleryImages($eventoId)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM eventos_galeria WHERE evento_id = ? ORDER BY orden ASC, fecha_creacion ASC");
        $stmt->execute([$eventoId]);
        return $stmt->fetchAll();
    }

    public static function addGalleryImage($eventoId, $data)
    {
        $db = getDB();
        $sql = "INSERT INTO eventos_galeria (evento_id, titulo, descripcion, imagen, orden)
                VALUES (:evento_id, :titulo, :descripcion, :imagen, :orden)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':evento_id' => $eventoId,
            ':titulo' => $data['titulo'] ?? null,
            ':descripcion' => $data['descripcion'] ?? null,
            ':imagen' => $data['imagen'],
            ':orden' => $data['orden'] ?? 0
        ]);
    }

    public static function updateGalleryImage($id, $data)
    {
        $db = getDB();
        $sql = "UPDATE eventos_galeria SET
                titulo = :titulo,
                descripcion = :descripcion,
                imagen = :imagen,
                orden = :orden
                WHERE id = :id";

        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }

    public static function deleteGalleryImage($id)
    {
        $db = getDB();
        $item = self::getGalleryImageById($id);

        if ($item && $item['imagen']) {
            $filePath = UPLOADS_DIR . $item['imagen'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $db->prepare("DELETE FROM eventos_galeria WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getGalleryImageById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM eventos_galeria WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
