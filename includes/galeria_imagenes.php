<?php

/**
 * Métodos adicionales para las imágenes de galería
 * Extiende la funcionalidad de la clase Galeria
 */

class GaleriaImagenes
{

    public static function getGalleryImages($galeriaId)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM galeria_imagenes WHERE galeria_id = ? ORDER BY orden ASC, fecha_creacion ASC");
        $stmt->execute([$galeriaId]);
        return $stmt->fetchAll();
    }

    public static function addGalleryImage($galeriaId, $data)
    {
        $db = getDB();
        $sql = "INSERT INTO galeria_imagenes (galeria_id, titulo, descripcion, imagen, orden)
                VALUES (:galeria_id, :titulo, :descripcion, :imagen, :orden)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':galeria_id' => $galeriaId,
            ':titulo' => $data['titulo'] ?? null,
            ':descripcion' => $data['descripcion'] ?? null,
            ':imagen' => $data['imagen'],
            ':orden' => $data['orden'] ?? 0
        ]);
    }

    public static function updateGalleryImage($id, $data)
    {
        $db = getDB();
        $sql = "UPDATE galeria_imagenes SET
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

        $stmt = $db->prepare("DELETE FROM galeria_imagenes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getGalleryImageById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM galeria_imagenes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
