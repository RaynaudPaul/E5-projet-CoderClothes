let links = document.querySelectorAll("[data-delete]");

for (let link of links){
    link.addEventListener("click", function (e){
        e.preventDefault();

        //console.log(link);

        if (confirm("Voulez-vous supprimer cette image ?")){
            fetch(this.getAttribute("href"), {
                method: "DELETE",
                headers: {
                    "X-requested-With": "XMLHttpRequest",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({"_token": this.dataset.token})
            }).then(response => response.json())
                .then(data => {
                    if (data.success){
                        this.parentElement.remove();
                    } else {
                        alert(data.error);
                    }
                })
        }
    });
}