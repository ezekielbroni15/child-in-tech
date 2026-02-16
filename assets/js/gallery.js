const galleryImages = [
  // Batch 1 (IMG_38xx)
  { src: "IMG_3824.JPG", category: "tech" },
  { src: "IMG_3825.JPG", category: "tech" },
  { src: "IMG_3826.JPG", category: "fun" },
  { src: "IMG_3827.JPG", category: "creative" },
  { src: "IMG_3828.JPG", category: "creative" },
  { src: "IMG_3829.JPG", category: "events" },
  { src: "IMG_3830.JPG", category: "events" },
  { src: "IMG_3831.JPG", category: "events" },
  { src: "IMG_3832.JPG", category: "events" },
  { src: "IMG_3833.JPG", category: "tech" },
  { src: "IMG_3834.JPG", category: "events" },
  { src: "IMG_3835.JPG", category: "tech" },
  { src: "IMG_3836.JPG", category: "tech" },
  { src: "IMG_3837.JPG", category: "tech" },
  { src: "IMG_3838.JPG", category: "tech" },
  { src: "IMG_3839.JPG", category: "tech" },
  { src: "IMG_3840.JPG", category: "events" },
  { src: "IMG_3841.JPG", category: "tech" },
  { src: "IMG_3842.JPG", category: "events" },
  { src: "IMG_3843.JPG", category: "fun" },
  { src: "IMG_3844.JPG", category: "creative" },
  { src: "IMG_3845.JPG", category: "creative" },
  { src: "IMG_3846.JPG", category: "events" },
  { src: "IMG_3847.JPG", category: "events" },
  { src: "IMG_3848.JPG", category: "events" },
  { src: "IMG_3849.JPG", category: "creative" },
  { src: "IMG_3850.JPG", category: "events" },
  { src: "IMG_3851.JPG", category: "events" },
  { src: "IMG_3852.JPG", category: "creative" },
  { src: "IMG_3853.JPG", category: "fun" },
  { src: "IMG_3854.JPG", category: "fun" },
  { src: "IMG_3855.JPG", category: "fun" },
  { src: "IMG_3856.JPG", category: "creative" },
  { src: "IMG_3857.JPG", category: "creative" },
  { src: "IMG_3858.JPG", category: "events" },
  { src: "IMG_3859.JPG", category: "tech" },
  { src: "IMG_3860.JPG", category: "events" },
  { src: "IMG_3861.JPG", category: "tech" },
  { src: "IMG_3862.JPG", category: "tech" },
  { src: "IMG_3863.JPG", category: "tech" },
  { src: "IMG_3864.JPG", category: "tech" },
  { src: "IMG_3865.JPG", category: "tech" },
  { src: "IMG_3866.JPG", category: "events" },
  { src: "IMG_3867.JPG", category: "tech" },
  { src: "IMG_3868.JPG", category: "events" },
  { src: "IMG_3869.JPG", category: "tech" },
  { src: "IMG_3870.JPG", category: "tech" },
  { src: "IMG_3871.JPG", category: "tech" },
  { src: "IMG_3872.JPG", category: "tech" },

  // Batch 2 (IMG_3881+)
  { src: "IMG_3881.JPG", category: "events" },
  { src: "IMG_3882.JPG", category: "tech" }, // Inferred
  { src: "IMG_3883.JPG", category: "events" },
  { src: "IMG_3884.JPG", category: "fun" },
  { src: "IMG_3885.JPG", category: "creative" },
  { src: "IMG_3886.JPG", category: "tech" },
  { src: "IMG_3887.JPG", category: "events" },
  { src: "IMG_3888.JPG", category: "fun" },
  { src: "IMG_3889.JPG", category: "creative" },
  { src: "IMG_3890.JPG", category: "tech" },
  { src: "IMG_3891.JPG", category: "events" },
  { src: "IMG_3892.JPG", category: "fun" },
  { src: "IMG_3893.JPG", category: "creative" },
  { src: "IMG_3894.JPG", category: "tech" },
  { src: "IMG_3895.JPG", category: "events" },
  { src: "IMG_3896.JPG", category: "fun" },
  { src: "IMG_3897.JPG", category: "creative" },
  { src: "IMG_3898.JPG", category: "tech" },
  { src: "IMG_3899.JPG", category: "events" },
  { src: "IMG_3900.JPG", category: "fun" },
  { src: "IMG_3901.JPG", category: "creative" },
  { src: "IMG_3902.JPG", category: "tech" },
  { src: "IMG_3903.JPG", category: "events" },
  { src: "IMG_3908.JPG", category: "fun" },

  // Batch 3 (IMG_66xx)
  { src: "IMG_6627.JPG", category: "events" },
  { src: "IMG_6580.JPG", category: "events" },
  { src: "IMG_6575.JPG", category: "events" },
  { src: "IMG_6594.JPG", category: "events" },
  { src: "IMG_6600.JPG", category: "events" },
  { src: "IMG_6655.JPG", category: "events" },

  // Batch 4 (New - IMG-66xx)
  { src: "IMG-6656.jpg", category: "events" },
  { src: "IMG-6657.jpg", category: "events" },
  { src: "IMG-6658.jpg", category: "events" },
  { src: "IMG-6659.jpg", category: "events" },
  { src: "IMG-6662.jpg", category: "events" },
  { src: "IMG-6663.jpg", category: "events" },
  { src: "IMG-6668.jpg", category: "events" },
  { src: "IMG-6669.jpg", category: "events" },
  { src: "IMG-6670.jpg", category: "events" },
  { src: "IMG-6671.jpg", category: "events" },
  { src: "IMG-6673.jpg", category: "events" },
  { src: "IMG-6674.jpg", category: "events" },
  { src: "IMG-6676.jpg", category: "events" },
  { src: "IMG-6677.jpg", category: "events" },
  { src: "IMG-6678.jpg", category: "events" },
  { src: "IMG-6679.jpg", category: "events" },
  { src: "IMG-6680.jpg", category: "events" },
  { src: "IMG-6681.jpg", category: "events" },

  // Misc
  { src: "img.jpg", category: "fun" },
  { src: "img-2.jpg", category: "events" },
  { src: "img-3.jpg", category: "events" },
  { src: "who-we-are.jpg", category: "events" },
];

function renderGallery() {
  const grid = document.getElementById("galleryGrid");
  const html = galleryImages
    .map(
      (img) => `
    <div class="gallery-item" data-category="${img.category}">
      <img
        src="assets/image/${img.src}"
        alt="Child In Tech Moment"
        loading="lazy"
      />
      <div class="gallery-overlay">
        <iconify-icon
          icon="lucide:maximize-2"
          class="expand-icon"
        ></iconify-icon>
      </div>
    </div>
  `,
    )
    .join("");
  grid.innerHTML = html;
}

// Run on load
document.addEventListener("DOMContentLoaded", renderGallery);
