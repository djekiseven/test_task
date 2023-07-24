async function getImage() {
    try {
      const response = await fetch("/main.php?action=getRandomImageId");
      if (!response.ok) {
        throw new Error("Failed to get random image ID");
      }
      const imageId = await response.json();
      const imagePath = `/src/${imageId}.jpg`;
  
      const image = document.getElementById("image");
      image.src = imagePath;
  
      await new Promise((resolve, reject) => {
        image.onload = resolve;
        image.onerror = reject;
      });
  
      // Изображение загружено успешно
      await fetch(`/main.php?action=increaseViewCount&imageId=${imageId}`);
  
      const viewCountValueElem = document.getElementById("viewCountValue");
      const updateViewCount = async () => {
        try {
          const response = await fetch(`/main.php?action=getViewCount&imageId=${imageId}`);
          if (!response.ok) {
            throw new Error("Failed to get view count");
          }
          const viewCount = await response.text();
          viewCountValueElem.textContent = viewCount;
        } catch (error) {
          console.error("Failed to update view count: ", error);
          clearInterval(intervalId);
        }
      };
  
      // Получаем текущее количество просмотров
      await updateViewCount();
  
      // Запускаем обновление счетчика
      const intervalId = setInterval(updateViewCount, 5000);
  
      // Останавливаем обновление счетчика при закрытии страницы
      window.addEventListener("beforeunload", () => {
        clearInterval(intervalId);
      });
    } catch (e) {
      document.getElementById("image").src = "/src/empty.jpg";
      document.getElementById("viewCountBlock").style.display = "none"
    }
  }
  
  getImage();