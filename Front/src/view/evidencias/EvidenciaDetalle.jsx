import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import EvidenciaCard from "../../components/EvidenciaCard";
import axios from "axios";

export default function EvidenciaDetalle() {
  const { id, name } = useParams();
  const [showForm, setShowForm] = useState(false);
  const [nombre, setNombre] = useState("");
  const [imagenes, setImagenes] = useState([]);
  const [data, setData] = useState([]);

  const handleSubmit = async (e) => {
    e.preventDefault();

    const formData = new FormData();
    formData.append("nombre", nombre);
    formData.append("carpeta_id", id);

    for (let i = 0; i < imagenes.length; i++) {
      formData.append("imagenes[]", imagenes[i]);
    }

    try {
      await axios.post("/evidencia.php", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      // recargar
      const res = await axios.get(`/evidencia.php`, {
        params: { carpeta_id: id },
      });
      setData({ evidencias: res.data });

      // limpiar
      setNombre("");
      setImagenes([]);
      setShowForm(false);
    } catch (err) {
      console.error("Error al guardar evidencia:", err);
    }
  };

  useEffect(() => {
    const fetchEvidencias = async () => {
      try {
        const res = await axios.get(`/evidencia.php`, {
          params: { carpeta_id: id },
        });
        setData({ evidencias: res.data });
      } catch (error) {
        console.error("Error al cargar evidencias:", error);
      }
    };

    fetchEvidencias();
  }, [id]);

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 py-6 space-y-6">
      {/* Título y botón */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-800">{name}</h1>
        <button
          onClick={() => setShowForm(!showForm)}
          className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
        >
          {showForm ? "Cancelar" : "Evidencia 📋​"}
        </button>
      </div>

      {/* Formulario para nueva evidencia */}
      {showForm && (
        <form
          onSubmit={handleSubmit}
          className="space-y-4 bg-gray-100 p-4 rounded shadow"
        >
          <div>
            <label className="block text-sm font-medium mb-1">
              Nombre de la evidencia
            </label>
            <input
              type="text"
              value={nombre}
              onChange={(e) => setNombre(e.target.value)}
              className="w-full border rounded px-3 py-2"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">
              Subir imágenes
            </label>
            <input
              type="file"
              multiple
              accept="image/*"
              onChange={(e) => setImagenes(e.target.files)}
              className="w-full"
              required
            />
          </div>

          <button
            type="submit"
            className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
          >
            Guardar evidencia
          </button>
        </form>
      )}

      {/* Lista de evidencias */}
      <div className="space-y-4">
        {data.evidencias?.map((evidencia) => (
          <EvidenciaCard key={evidencia.id} evidencia={evidencia} />
        ))}
      </div>
    </div>
  );
}
