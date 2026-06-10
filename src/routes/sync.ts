import express, { Request, Response } from 'express';
import { Pool } from 'pg';

const router = express.Router();
// Initialize connection pooler to PostgreSQL instance
const dbPool = new Pool({ connectionString: process.env.DATABASE_URL });

router.post('/api/v1/sync-ledger', async (req: Request, res: Response) => {
    const { walletId, transactions } = req.body;

    if (!walletId || !Array.isArray(transactions)) {
        return res.status(400).json({ error: "Invalid sync request payload configuration." });
    }

    // Get a dedicated connection client from the pool to handle our cloud transaction
    const client = await dbPool.connect();

    try {
        // 1. Open an atomic database transaction layer on the cloud database
        await client.query('BEGIN');

        for (const tx of transactions) {
            // 2. Idempotency Check: Verify if this transaction ID was already processed
            // (prevents duplicate charging if network dropped during a previous sync)
            const duplicateCheck = await client.query(
                'SELECT 1 FROM cloud_ledger_transactions WHERE transaction_id = $1',
                [tx.transaction_id]
            );

            if (duplicateCheck.rows.length > 0) {
                continue; // Skip this record, it's already safely written to the server ledger
            }

            // 3. Write record into the centralized cloud ledger audit history
            await client.query(
                `INSERT INTO cloud_ledger_transactions (transaction_id, wallet_id, amount, type) 
         VALUES ($1, $2, $3, $4)`,
                [tx.transaction_id, walletId, tx.amount, tx.type]
            );

            // 4. Update the master balance matching state based on transaction type
            if (tx.type === 'MICRO_DEDUCTION') {
                await client.query(
                    'UPDATE wallets SET current_balance = current_balance - $1 WHERE wallet_id = $2',
                    [tx.amount, walletId]
                );
            } else if (tx.type === 'TOP_UP') {
                await client.query(
                    'UPDATE wallets SET current_balance = current_balance + $1 WHERE wallet_id = $2',
                    [tx.amount, walletId]
                );
            }
        }

        // 5. Commit all records simultaneously to PostgreSQL
        await client.query('COMMIT');
        return res.status(200).json({ status: "SUCCESS", message: "Ledger items processed cleanly." });

    } catch (error) {
        // Roll back changes completely if any database query crashes
        await client.query('ROLLBACK');
        console.error("Cloud Database Ingestion Error:", error);
        return res.status(500).json({ error: "Internal processing engine error." });
    } finally {
        // Release client socket back to connection pool
        client.release();
    }
});

export default router;