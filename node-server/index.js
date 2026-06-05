import express from "express";
import cors from "cors";
import { pipeline } from "@huggingface/transformers";

const app = express();
const PORT = 5000;

app.use(cors());
app.use(express.json());

let extractor = null;

async function initializeModel() {
    try {
        console.log("Loading all-MiniLM-L6-v2 model into memory...");
        extractor = await pipeline(
            "feature-extraction",
            "Xenova/all-MiniLM-L6-v2",
        );
        console.log("Model loaded successfully! Ready for embedding requests.");
    } catch (error) {
        console.error("Failed to load the embedding model:", error);
        process.exit(1); // Exit if the model fails to load
    }
}

app.post("/embed", async (req, res) => {
    const { content } = req.body;
    console.log("Received embedding request for content:", content);

    if (!content || typeof content !== "string" || content.trim() === "") {
        return res.status(400).json({
            error: 'Bad Request: Missing or invalid "content" string field.',
        });
    }

    try {
        // Generate the feature extraction tensor
        const output = await extractor(content, {
            pooling: "mean",
            normalize: true,
        });

        // Extract the raw data float array out of the tensor object
        const embedding = Array.from(output.data);

        // Double check dimension integrity before responding
        if (embedding.length !== 384) {
            throw new Error(
                `Model output dimension expected 384, but got ${embedding.length}`,
            );
        }

        return res.json({
            success: true,
            content: content,
            dimension: embedding.length,
            embedding: embedding,
        });
    } catch (error) {
        console.error("Embedding Generation Error:", error);
        return res.status(500).json({
            error: "Internal Server Error during embedding generation.",
        });
    }
});

// Boot up sequence
initializeModel().then(() => {
    app.listen(PORT, () => {
        console.log(
            `Embedding microservice actively listening on port ${PORT}`,
        );
    });
});
