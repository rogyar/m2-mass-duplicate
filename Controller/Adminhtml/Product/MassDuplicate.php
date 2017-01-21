<?php
namespace Atwix_MassDuplicate\Controller\Adminhtml\Product;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\Product\Copier;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Psr\Log\LoggerInterface;

class Duplicate extends BackendAction
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Copier
     */
    protected $productsCopier;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Copier $copier,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {

        $this->$collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->productsCopier = $copier;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productsDuplicated = 0;
        try {
            foreach ($collection->getItems() as $product) {
                $this->productsCopier->copy($product);
                $productsDuplicated++;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been duplicated.', $productsDuplicated)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }
}
