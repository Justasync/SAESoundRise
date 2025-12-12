import os
import requests
import time

def descargar_imagenes(cantidad=40, url_base="https://picsum.photos/500"):
    # Nombre de la carpeta donde se guardarán
    carpeta = "albumes_prueba"
    
    # Crear la carpeta si no existe
    if not os.path.exists(carpeta):
        os.makedirs(carpeta)
        print(f"Carpeta '{carpeta}' creada.")
    
    print(f"Iniciando la descarga de {cantidad} imágenes...")

    for i in range(1, cantidad + 1):
        try:
            # Hacemos la petición a la URL
            response = requests.get(url_base, allow_redirects=True)
            
            # Verificamos que la petición fue exitosa (código 200)
            if response.status_code == 200:
                nombre_archivo = f"{carpeta}/album_{i}.jpg"
                
                # Guardamos el contenido binario de la imagen
                with open(nombre_archivo, 'wb') as f:
                    f.write(response.content)
                
                print(f"[{i}/{cantidad}] ✅ Descargada: {nombre_archivo}")
            else:
                print(f"[{i}/{cantidad}] ❌ Error: El servidor devolvió código {response.status_code}")
            
        except Exception as e:
            print(f"[{i}/{cantidad}] ❌ Error al descargar: {e}")

    print("\n¡Proceso finalizado! Revisa la carpeta 'albumes_prueba'.")

if __name__ == "__main__":
    descargar_imagenes()